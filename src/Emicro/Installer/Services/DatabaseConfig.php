<?php
/**
 * @Author: Roni Kumar Saha
 * Date: 9/6/13
 * Time: 7:12 PM
 */

namespace Emicro\Installer\Services;

use Composer\Script\Event;
use Emicro\Installer\Manager;

class DatabaseConfig
{
    private static $configurableDatabaseOptions = array(
        'hostname'=>'The hostname of your database server',
        'username'=>'The username used to connect to the database server',
        'password'=>'The password used to connect to the database server',
        'database'=>'The name of the database you want to connect to'
    );


    public static function write(Event $event, $appDirectory)
    {
        self::checkDatabaseConfigurationFiles($appDirectory);

        $dist_config = self::getDatabaseConfigurationData($appDirectory . '/config/database.php.dist');
        $current_config = self::getDatabaseConfigurationData($appDirectory . '/config/database.php');
        $changed_config = self::getWritableDatabaseConfigurationValues($event, $dist_config, $current_config);

        if(empty($changed_config)){ //Nothing change
            return false;
        }

        $configData = file_get_contents(Manager::getResourcePath('database.php', '/config'));

        $buildDatabaseConfigurationString = self::buildDatabaseConfigurationString($changed_config);
        $configData = str_replace('{DB_CONFIG_DATA}', $buildDatabaseConfigurationString, $configData);

        file_put_contents($appDirectory . '/config/database.php', $configData);

    }

    private static function buildDatabaseConfigurationString($configs)
    {
        $configTemplate = '$db[\'default\'][\'%s\'] = ';

        $str = "";
        foreach($configs as $key => $config){
            $str .= sprintf($configTemplate, $key);
            if(is_bool($config)){
                $str .=  ($config ? 'TRUE' : 'FALSE') . ";" . PHP_EOL;
            }else{
                $str .=  "'$config';" . PHP_EOL;
            }
        }

        return $str;

    }

    public static function validateDatabaseConfiguration($value, $type)
    {
        switch ($type):
            case 'dbdriver':
                return in_array($value, array('mysql', 'mysqli', 'postgre', 'odbc', 'mssql', 'sqlite', 'oci8'));
                break;
            default:
                return true;
        endswitch;
    }

    private static function getWritableDatabaseConfigurationValues(Event $event, $dist_config, $current_config)
    {
        $changed_config = array();
        $io = $event->getIO();

        $firstEntry = true;

        foreach($dist_config as $key => $config){

            if(!isset(self::$configurableDatabaseOptions[$key]) && $config !== '~'){
                    continue;
            }

            if(!isset($current_config[$key]) || $current_config[$key] == '~'){
                if($firstEntry){
                    $message = Colors::message("%s ").Colors::info('"%s"').Colors::message(" File");

                    if(empty($current_config)){
                        $typeStr = "Generating";
                        $message2 = Colors::info("Enter Database Configuration options :");
                    }else{
                        $typeStr = "Updating";
                        $message2 = Colors::info("Some parameters are missing. Please provide them :");
                    }

                    $io->write(PHP_EOL.sprintf($message, $typeStr, 'config/database.php'));
                    $io->write($message2);
                    $firstEntry = false;
                }

                $default_value = $config == "~" || $config == "" ? 'null' : $config;

                if(isset(self::$configurableDatabaseOptions[$key])){
                    $question = sprintf('Enter %s [%s] :',
                                              Colors::highlight(self::$configurableDatabaseOptions[$key]),
                                              Colors::info($default_value));
                }else{
                    $question = sprintf('Enter value for "%s" [%s] :', Colors::highlight($key), Colors::info($default_value));
                }

                $data = $io->ask($question, $config);

                if(!self::validateDatabaseConfiguration($data,$key)){
                    $data = $config;
                }

                $changed_config[$key] = $data;
            }
        }

        if(!empty($changed_config)){
            $changed_config = array_merge($dist_config, $current_config, $changed_config);
        }

        return $changed_config;
    }

    private static function checkDatabaseConfigurationFiles($appDirectory)
    {
        if(!file_exists($appDirectory . '/config/database.php.dist')){
            $dist_file = realpath(__DIR__ . "/../Resources/app/config/database.php.dist");
            copy($dist_file, $appDirectory . '/config/database.php.dist');
        }
    }

    private static function getDatabaseConfigurationData($file)
    {
        if (file_exists($file)) {
            include $file;
        }

        if (isset($db['default'])) {
            return $db['default'];
        }

        return array();
    }
}