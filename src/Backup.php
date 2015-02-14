<?php

namespace Journey;

use PDO;
use PDOException;
use Aws\S3\S3Client;

class Backup
{

    private $conf;

    private $s3;

    private $exclude = array(
        'information_schema',
        'performance_schema'
    );


    /**
     * Setup our primary backup controller
     * @param Array $conf configuration array
     */
    public function __construct($conf)
    {
        if (empty($conf['databases'])) {
            try {
                $connection = new PDO('mysql:host=' . $conf['connection']['host'], $conf['connection']['username'], $conf['connection']['password']);
                $databases = $connection->query('SHOW DATABASES')->fetchAll(PDO::FETCH_ASSOC);
                $conf['databases'] = array_map(function ($element) {
                    return $element['Database'];
                }, $databases);
            } catch (PDOException $e) {
                echo "Unable to connect to the database: " . $e->getMessage() . "\n";
            }
        }

        if (substr($conf['storage'], 0, 5) == 's3://') {
            $this->s3 = S3Client::factory($conf['aws']);
            $this->s3->registerStreamWrapper();
        }

        // Configure the dump global settings
        $this->conf = $conf;
        $this->dump();
    }



    /**
     * Dump the contents of each database
     * @return  [description]
     */
    public function dump()
    {
        $dumpConfig = [
            'username' => $this->conf['connection']['username'],
            'password' => $this->conf['connection']['password'],
            'location' => $this->conf['temp']
        ];

        Dump::config($dumpConfig);

        foreach ($this->conf['databases'] as $database) {
            if (!in_array($database, $this->exclude)) {
                $file = Dump::db($database);
                $this->store($file);
            }
        }
    }



    /**
     * Store a a file according to our hierarchy rules at the desired location
     * @param  String $path absolute path to the file
     * @return Boolean      success or failure to move
     */
    public function store($path)
    {
        $location = $this->getStoragePath($path);
        if (!$this->s3) {
            if (!rename($path, $location)) {
                throw new BackupException('Unable to move ' . $path . ' to ' . $location);
            }
        } else {
            $segments = explode("/", substr($location, 5));
            $bucket = $segments[0];
            unset($segments[0]);
            $key = implode("/", $segments);
            $this->s3->putObject([
                'Bucket' => $bucket,
                'Key' => $key,
                'Body' => fopen($path, 'r+')
            ]);
        }
    }



    /**
     * Get the storage path of the destination location for the backups
     * @param  String $path local absolute path of the sql dump
     * @return String       local or remote path (or stream) to place the dump for storage
     */
    public function getStoragePath($path)
    {
        $location = $this->conf['storage'];
        $path = pathinfo($path);
        $database = $path['filename'];
        $location .= (substr($location, -1) == '/') ? '':'/' . $database . '/';
        if (!$this->s3 && !is_dir($location)) {
            mkdir($location, 0777, true);
        }
        $location .= date('Y-m-d-') . $database . '.sql';
        return $location;
    }
}
