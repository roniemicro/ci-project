<?php
/**
 * @Author: Roni Kumar Saha
 *        Date: 7/15/13
 *        Time: 3:41 PM
 */

namespace Emicro\Installer;

use Composer\Script\Event;
use Emicro\Installer\Services\Colors;
use Emicro\Installer\Services\CoreLibrary;
use Emicro\Installer\Services\DataBase;
use Emicro\Installer\Services\DatabaseConfig;
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

            $confirmQuestion = sprintf('Do you want %s "%s" library to your application(yes,no)?[%s] :',
                self::$msg[$installStatusStr], Colors::info($label), Colors::info($installStatusStr));

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

        CoreLibrary::manage($event, $extras, self::$newCopy);
        DatabaseConfig::write($event, $appDirectory);

        self::generateDatabaseSchema($appDirectory, $event);

        $uploadDir = realpath($webDirectory) . DIRECTORY_SEPARATOR . "uploads";

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir);
        }
    }


    private static function generateDatabaseSchema($appDirectory, Event $event)
    {
        $io = $event->getIO();

        $current_config = DatabaseConfig::getDatabaseConfigurationData($appDirectory . '/config/database.php');

        $db = new DataBase($current_config);

        if(!$db->isConnected()){
            $io->write(Colors::error("Database connect failed with following error:"));
            $io->write(Colors::info($db->getConnectionError()));
            return;
        }

        $dbCreated = true;

        if(!$db->selectDB($current_config['database'])){
            $confirmMsg     = sprintf('Do you like me to create database %s for you(yes,no)?[yes] :', Colors::info('"%s"'));
            $confirmMsg =   sprintf($confirmMsg, $current_config['database']);

            if($io->askConfirmation($confirmMsg, TRUE)){
                if($dbCreated = $db->createDB($current_config['database'])){
                    $io->write(Colors::success("Database Created successfully!"));
                }else{
                    $io->write(Colors::error("Database creation failed!"));
                }
            }else{
                $dbCreated = false;
            }
        }

        if(!$dbCreated){ //No database so we have nothing to do!
            return;
        }

        $tables = DatabaseConfig::getEzRbacTableNames($appDirectory);

        if(!$db->checkTable($tables['user_table']))
        {
            $confirmMsg = "Do you want me to create schema for following Tables " .PHP_EOL . "[";
            $confirmMsg .= Colors::info(implode(',', $tables));
            $confirmMsg .= "]" . PHP_EOL . "(yes,no)?[yes] :";

            if($io->askConfirmation($confirmMsg, TRUE)){
                $db->parseMysqlDump($appDirectory . "/third_party/ezRbac/schema/tables.sql" );
                $db->parseMysqlDump($appDirectory . "/third_party/ezRbac/schema/data.sql" );
                $io->write(Colors::success("Schema updated successfully!"));
            }
        }
    }

    private static function buildBootstrap($event, $bootstrap, $extras, $webDirectory, $ciBasePath)
    {
        $io = $event->getIO();

        if (file_exists($bootstrap)) {
            $writeMode = "Updating";
            $confirmMsg     = Colors::confirm("Re-Write Bootstrap File(yes,no)?[no]") . " :";
            $writeBootstrap = $io->askConfirmation($confirmMsg, FALSE);
            if ($writeBootstrap) {
                $data = file_get_contents($bootstrap);
                preg_match("/define\('ENVIRONMENT',(\s)*'([a-z]+)'\);/", $data, $matches);

                if(isset($matches[2])){
                    self::$environment = $matches[2];
                }
            }
        } else {
            $writeMode = "Writing";
            $writeBootstrap = TRUE;
        }

        if ($writeBootstrap) {
            $confirmMsg = sprintf("Set application environment to(production,development,testing)?[%s] :", Colors::info(self::$environment));
            $env = $io->ask($confirmMsg, self::$environment);

            if (in_array($env, self::$validEnvironments)) {
                self::$environment = $env;
            } else {
                $msg = Colors::error("Invalid selection!"). PHP_EOL;
                $msg .= Colors::message("Setting the environment to : ") . Colors::highlight(self::$environment);
                $io->write($msg);
            }

            $io->write(Colors::message(sprintf("%s Bootstrap File ", $writeMode)).PHP_EOL);
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

    private static function getIndexTemplate()
    {
        return file_get_contents(__DIR__ . "/Resources/index.php.tpl");
    }

    public static function getResourcePath($baseName, $baseDir = "")
    {
        return realpath(__DIR__ . "/Resources{$baseDir}/$baseName.tpl");
    }
}