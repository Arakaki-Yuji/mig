<?php

namespace Mig;

/**
 * Config class.
 *
 * this class read "mig.config.php" file as a config file.
 *
 */
class Config
{
    const DEFAULT_CONFIG_PATH = './';
    const CONFIG_FILE_NAME = 'mig.config.php';
    /**
     * default file path for migration files.
     */
    private $migration_filepath = "migrations";

    private $db_dsn;
    private $db_username;
    private $db_passwd;

    public function __construct($path = null) {
        if(!is_null($path))
        {
            $this->path = $path;
        }
       $c =  $this->read_config();
       $this->db_dsn = $c['db_dsn'];
       $this->db_username = $c['db_username'];
       $this->db_passwd = $c['db_passwd'];
       $this->migration_filepath = $c['migration_filepath'];
    }

    public function getMigrationFilePath()
    {
        return $this->migration_filepath;
    }

    public function getDbDsn()
    {
        return $this->db_dsn;
    }

    public function getDbUsername()
    {
        return $this->db_username;
    }

    public function getDbPasswd()
    {
        return $this->db_passwd;
    }

    public function read_config()
    {
        $c = require(WORKING_DIR. '/'. self::CONFIG_FILE_NAME);
        return $c;
    }
}