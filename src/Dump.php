<?php

namespace Journey;

class Dump
{

    /**
     * Static method to create a new instance of the dump object
     * @param  String $db      database name to dump
     * @param  array  $options overrides for any of the global options
     * @return String          Local filesystem location of the output
     */
    public static function db($database, $options = array())
    {
        $config = array_replace_recursive(Dump::config(), $options);
        $output = $config['location'] . '/' . $database . '.sql';
        $command = sprintf(
            "mysqldump -u %s -p%s %s > %s",
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($database),
            escapeshellarg($output)
        );

        $commandReturn = array();
        exec($command, $commandReturn, $return);
        if ($return) {
            throw new DumpException('An error occured durring the dumping database: ' . $database);
        }

        return $output;
    }



    /**
     * Configure and or return configuration options
     * @param  array  $options array of options to provide globally
     * @return array           a merged array of global options
     */
    public static function config($options = array())
    {
        static $config;
        
        if ($options) {
            if ($config) {
                $config = array_replace_recursive($config, $options);
            } else {
                $config = $options;
            }
        }

        return $config;
    }
}
