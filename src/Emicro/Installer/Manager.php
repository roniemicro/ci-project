<?php
/**
 * @Author: Roni Kumar Saha
 *        Date: 7/15/13
 *        Time: 3:41 PM
 */

namespace Emicro\Installer;

use Composer\Script\Event;
use Emicro\Helper\Filesystem;

class Manager
{
    private static $requires = array(
        'twig/twig' => 'Twig',
        'swiftmailer/swiftmailer' => 'Swift Mailer',
        'dompdf/dompdf' => 'DOM Pdf'
    );

    private static $msg = array(
        'yes' => 'keep',
        'no' => 'add',
    );

    private static $twigInstalled = false;
    private static $newCopy = false;
    private static $environment = 'production';
    private static $validEnvironments = array('production', 'development', 'testing');
    private static $coreLibraries = array('Controller', 'Loader', 'Model');

    public static function postUpdate(Event $event)
    {
        self::configureEnvironment($event);
    }

    public static function postInstall(Event $event)
    {
        self::configureEnvironment($event);
    }

    public static function preInstall(Event $event)
    {
        self::updateRequiresLibraries($event);
    }

    public static function preUpdate(Event $event)
    {
        self::updateRequiresLibraries($event);
    }

    private static function updateRequiresLibraries(Event $event)
    {
        $package = $event->getComposer()->getPackage();
        $requiresList = $package->getRequires();
        $io = $event->getIO();

        $package->setExtra(self::getExtras($event));

        foreach (self::$requires as $require => $label) {
            $installStatus = self::isInstalled($requiresList[$require]);

            if($require == 'twig/twig'){
                self::$twigInstalled = $installStatus;
            }

            $installStatusStr = $installStatus ? 'yes' : 'no';

            $confirmQuestion = sprintf('Do you want %s "%s" library to your application(yes,no)[%s]?',
                self::$msg[$installStatusStr], $label, $installStatusStr);

            $answer = !$io->askConfirmation($confirmQuestion, $installStatus);

            if ($answer) {
                unset($requiresList[$require]);
            }
        }

        $extras = $package->getExtra();

        foreach ($extras['installer-paths'] as $path => $packs) {
            foreach ($packs as $name) {
                if (isset($requiresList[strtolower($name)]) && self::isInstalledPackage($path, $name)) {
                    unset($requiresList[strtolower($name)]);
                }
            }
        }

        $package->setRequires($requiresList);
    }

    private static function isInstalledPackage($path, $package)
    {
        list($vendor, $name) = explode('/', $package);
        $path = str_replace(array('{$name}', '{$vendor}'), array($name, $vendor), $path);
        return file_exists($path);
    }

    private static function getExtras(Event $event)
    {
        $extras = $event->getComposer()->getPackage()->getExtra();

        $thirdPartyDir = $extras['ci-app-dir'] . '/third_party/{$name}';
        $extras['installer-paths'][$thirdPartyDir] = $extras['installer-paths']['CI_THIRD_PARTY_PATH'];
        unset($extras['installer-paths']['CI_THIRD_PARTY_PATH']);

        return $extras;
    }

    private static function isInstalled($link)
    {
        return file_exists("vendor/" . $link->getTarget());
    }

    private static function configureEnvironment($event)
    {
        $extras = $event->getComposer()->getPackage()->getExtra();

        $bootstrap = $extras['ci-web-dir'] . "/index.php";
        $webDirectory = realpath(".") . DIRECTORY_SEPARATOR . $extras['ci-web-dir'];
        $appDirectory = realpath(".") . DIRECTORY_SEPARATOR . $extras['ci-app-dir'];
        $ciBasePath = 'vendor/' . $extras['ci-package-name'];

        self::copyAppDir($ciBasePath, $appDirectory);
        self::buildBootstrap($event, $bootstrap, $extras, $webDirectory, $ciBasePath);
        self::installCoreLibraries($event, $extras);

        $uploadDir = realpath($webDirectory) . DIRECTORY_SEPARATOR . "uploads";

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir);
        }
    }

    private static function buildBootstrap($event, $bootstrap, $extras, $webDirectory, $ciBasePath)
    {
        $io = $event->getIO();

        if (file_exists($bootstrap)) {
            $writeBootstrap = $io->askConfirmation("Re-Write Bootstrap File(yes,no)?[no]", FALSE);
            if ($writeBootstrap) {
                $data = file_get_contents($bootstrap);
                preg_match("/define\('ENVIRONMENT',(\s)*'([a-z]+)'\);/", $data, $matches);
                self::$environment = $matches[2];
            }
        } else {
            $writeBootstrap = TRUE;
        }

        if ($writeBootstrap) {
            $env = $io->ask("Set application environment to(production,development,testing)?[" . self::$environment . "]", self::$environment);

            if (in_array($env, self::$validEnvironments)) {
                self::$environment = $env;
            } else {
                $io->write("Invalid selection. Setting the environment to " . self::$environment);
            }

            self::writeBootstrap($bootstrap, $extras, $webDirectory, $ciBasePath);
        }
    }

    private static function copyAppDir($ciBasePath, $appDirectory)
    {
        if (!file_exists($appDirectory . "/controllers")) {
            $source = realpath($ciBasePath . '/application');

            Filesystem::copyDirectory($source, $appDirectory);
            Filesystem::copyDirectory(realpath(__DIR__ . "/Resources/app"), $appDirectory);

            $configFileTpl = self::$twigInstalled ? 'config.php.twig' : 'config.php';
            $configSource = self::getResourcePath($configFileTpl, '/config');
            $configDest = $appDirectory . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . 'config.php';

            copy($configSource, $configDest);

            self::$newCopy = true;
        }
    }

    private static function writeBootstrap($bootstrap, $extras, $webDirectory, $ciBasePath)
    {
        $template = self::getIndexTemplate();

        $applicationDirectory = Filesystem::getRelativePath(realpath($extras['ci-app-dir']), $webDirectory);
        $systemDirectory = Filesystem::getRelativePath(realpath("{$ciBasePath}/system"), $webDirectory);

        $template = str_replace(array('{APPLICATION_ENV}', '{CI_SYSTEM_PATH}', '{APPLICATION_DIR}'),
            array(self::$environment, $systemDirectory, $applicationDirectory),
            $template);

        file_put_contents($bootstrap, $template);
    }

    private static function installCoreLibraries($event, $extras)
    {
        $ciAppDir = realpath($extras['ci-app-dir']) . DIRECTORY_SEPARATOR;

        $libBaseDir = $ciAppDir . "core" . DIRECTORY_SEPARATOR;

        foreach (self::$coreLibraries as $library) {
            self::installCoreLibrary($library, $libBaseDir);
        }

        if ($extras['localize-ready']) {
            self::installCoreLibrary('Lang', $libBaseDir);
            $routeSource = self::getResourcePath('routes.php.mu', '/config');
        } else {
            self::removeCoreLibrary('Lang', $libBaseDir);
            $routeSource = self::getResourcePath('routes.php', '/config');
        }

        $routeDest = $ciAppDir . "config" . DIRECTORY_SEPARATOR . 'routes.php';

        $writeRoute = TRUE;

        if(!self::$newCopy){
            if (file_exists($routeDest)) {
                $io = $event->getIO();
                $writeRoute = $io->askConfirmation("Re-Write Route Configuration File(yes,no)?[no]", FALSE);
            }
        }

        if ($writeRoute) {
            copy($routeSource, $routeDest);
        }

    }

    private static function installCoreLibrary($library, $libBaseDir)
    {
        $file = "MY_" . $library . '.php';
        $libDest = $libBaseDir . $file;
        if (!file_exists($libDest)) {
            $source = self::getResourcePath($file, '/core');
            copy($source, $libDest);
        }
    }

    private static function removeCoreLibrary($library, $libBaseDir)
    {
        $file = "MY_" . $library . '.php';
        $libDest = $libBaseDir . $file;

        if (file_exists($libDest)) {
            unlink($libDest);
        }
    }

    private static function getIndexTemplate()
    {
        return file_get_contents(__DIR__ . "/Resources/index.php.tpl");
    }

    private static function getResourcePath($baseName, $baseDir = "")
    {
        return realpath(__DIR__ . "/Resources{$baseDir}/$baseName.tpl");
    }
}