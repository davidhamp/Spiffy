<?php

namespace SPF\Core;

use \PDO;
use SPF\Exceptions\DatabaseException;

class Database
{

    protected $dbConfig;

    /**
     * Constructor
     *
     * @dmManaged
     * @dmRequires SPF\Core\Configuration @config
     *
     * @method __construct
     */
    public function __construct($config)
    {
        $this->dbConfig = $config->get('databases');
    }

    /**
     * Returns a PDO connection to the corresponding mysql database defined in the config
     *
     * If no id is provided, it will default to the first db defined.
     *
     * If no db is defined, this will throw an exception
     *
     * @method getMysqlConnection
     *
     * @param  int $id Database id from the configuration (array index)
     *
     * @return PDO PDO Connection instance
     */
    public function getMysqlConnection($id = 0)
    {
        if (array_key_exists('mysql', $this->dbConfig) && is_array($this->dbConfig['mysql'])) {
            $dbInfo = $this->dbConfig['mysql'][$id];

            if (!isset($dbInfo['host'])
                || !isset($dbInfo['port'])
                || !isset($dbInfo['username'])
                || !isset($dbInfo['password'])
                || !isset($dbInfo['database'])
            ) {
                throw new DatabaseException('There is missing required information from the database configuration');
            }

            return new PDO(
                'mysql:host=' . $dbInfo['host'] . ';port=' . $dbInfo['port'] . ';dbname=' . $dbInfo['database'],
                $dbInfo['username'],
                $dbInfo['password']
            );
        }

    }

}