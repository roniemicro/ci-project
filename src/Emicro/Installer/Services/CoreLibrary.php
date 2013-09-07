<?php
/**
 * @Author: Roni Kumar Saha
 * Date: 9/6/13
 * Time: 7:23 PM
 */

namespace Emicro\Installer\Services;

use Composer\Script\Event;
use Emicro\Installer\Manager;

class CoreLibrary {

    private static $coreLibraries = array('Controller', 'Loader', 'Model');

    public static function manage(Event $event, $extras, $newCopy)
    {
        $ciAppDir = realpath($extras['ci-app-dir']) . DIRECTORY_SEPARATOR;

        $libBaseDir = $ciAppDir . "core" . DIRECTORY_SEPARATOR;

        foreach (self::$coreLibraries as $library) {
            self::install($library, $libBaseDir);
        }

        if ($extras['localize-ready']) {
            self::install('Lang', $libBaseDir);
            $routeSource = Manager::getResourcePath('routes.php.mu', '/config');
        } else {
            self::remove('Lang', $libBaseDir);
            $routeSource = Manager::getResourcePath('routes.php', '/config');
        }

        $routeDest = $ciAppDir . "config" . DIRECTORY_SEPARATOR . 'routes.php';

        $writeRoute = TRUE;

        $io = $event->getIO();

        if(!$newCopy){
            $writeMode = "Updating";
            if (file_exists($routeDest)) {
                $confirmMsg     = Colors::confirm("Re-Write Route Configuration File(yes,no)?[no]") . " :";
                $writeRoute = $io->askConfirmation($confirmMsg, FALSE);
            }
        }else{
            $writeMode = PHP_EOL."Writing";
        }

        if ($writeRoute) {
            $io->write(Colors::message(sprintf("%s Route Configuration File ", $writeMode)).Colors::info('"config/routes.php"'));
            copy($routeSource, $routeDest);
        }

    }

    private static function install($library, $libBaseDir)
    {
        $file = "MY_" . $library . '.php';
        $libDest = $libBaseDir . $file;
        if (!file_exists($libDest)) {
            $source = Manager::getResourcePath($file, '/core');
            copy($source, $libDest);
        }
    }

    private static function remove($library, $libBaseDir)
    {
        $file = "MY_" . $library . '.php';
        $libDest = $libBaseDir . $file;

        if (file_exists($libDest)) {
            unlink($libDest);
        }
    }
}