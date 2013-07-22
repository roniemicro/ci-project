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
            'twig/twig'=>'Twig',
            'swiftmailer/swiftmailer'=>'Swift Mailer',
            'dompdf/dompdf'=>'DOM Pdf'
    );

    private static $msg = array(
        'yes'=>'keep',
        'no'=>'add',
    );

    public static function postUpdate(Event $event)
    {
        self::configureEnvironment($event);
    }

    public static function postInstall(Event $event)
    {
        self::configureEnvironment($event);
    }

    public static function preInstall(Event $event){
        self::updateRequiresLibraries($event);
    }

    public static function preUpdate(Event $event){
        self::updateRequiresLibraries($event);
    }

    private static function updateRequiresLibraries(Event $event)
    {
        $package = $event->getComposer()->getPackage();
        $requiresList = $package->getRequires();
        $io = $event->getIO();

        foreach (self::$requires as $require=>$label){
            $installStatus = self::isInstalled($requiresList[$require]);
            $installStatusStr = $installStatus ? 'yes':'no';

            $confirmQuestion = sprintf('Do you want %s "%s" library to your application(yes,no)[%s]?',
                                        self::$msg[$installStatusStr],$label, $installStatusStr);

            $answer = !$io->askConfirmation($confirmQuestion, $installStatus);

            if($answer){
                unset($requiresList[$require]);
            }
        }

        $package->setExtra(self::getExtras($event));

        $package->setRequires($requiresList);
    }

    private static function getExtras(Event $event)
    {
        $extras = $event->getComposer()->getPackage()->getExtra();

        $thirdPartyDir = $extras['ci-app-dir'].'/third_party/{$name}';
        $extras['installer-paths'][$thirdPartyDir]= $extras['installer-paths']['CI_THIRD_PARTY_PATH'];
        unset($extras['installer-paths']['CI_THIRD_PARTY_PATH']);

        return $extras;
    }

    private static function isInstalled($link)
    {
        return file_exists("vendor/".$link->getTarget());
    }

    private static function configureEnvironment($event)
    {
        $extras = $event->getComposer()->getPackage()->getExtra();

        $bootstrap    = $extras['ci-web-dir'] . "/index.php";
        $webDirectory = realpath(".") . DIRECTORY_SEPARATOR . $extras['ci-web-dir'];
        $appDirectory = realpath(".") . DIRECTORY_SEPARATOR . $extras['ci-app-dir'];
        $ciBasePath   = 'vendor/' . $extras['ci-package-name'];

        self::copyAppDir($ciBasePath, $appDirectory);
        self::buildBootstrap($event, $bootstrap, $extras, $webDirectory, $ciBasePath);

        $uploadDir = realpath($webDirectory).DIRECTORY_SEPARATOR."uploads";

        if(!file_exists($uploadDir)){
            mkdir($uploadDir);
        }
    }

    private static function buildBootstrap($event, $bootstrap, $extras, $webDirectory, $ciBasePath)
    {
        if (file_exists($bootstrap)) {
            $io             = $event->getIO();
            $writeBootstrap = $io->askConfirmation("Re-Write Bootstrap File(yes,no)?[no]", FALSE);
        } else {
            $writeBootstrap = TRUE;
        }

        if ($writeBootstrap) {
            self::writeBootstrap($bootstrap, $extras, $webDirectory, $ciBasePath);
        }
    }

    private static function copyAppDir($ciBasePath, $appDirectory)
    {
        if (!file_exists($appDirectory)) {
            $source = realpath($ciBasePath . '/application');
            Filesystem::copyDirectory($source, $appDirectory);
        }
    }

    private static function writeBootstrap($bootstrap, $extras, $webDirectory, $ciBasePath)
    {
        $template = self::getIndexTemplate();

        $applicationDirectory = Filesystem::getRelativePath(realpath($extras['ci-app-dir']), $webDirectory);
        $systemDirectory      = Filesystem::getRelativePath(realpath("{$ciBasePath}/system"), $webDirectory);

        $template = str_replace(array('{CI_SYSTEM_PATH}', '{APPLICATION_DIR}'),
            array($systemDirectory, $applicationDirectory),
            $template);

        file_put_contents($bootstrap, $template);
    }

    private static function getIndexTemplate()
    {
        return file_get_contents(__DIR__ . "/Resources/index.php.tpl");
    }
}