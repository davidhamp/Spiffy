<?php
/**
 * SPF/Core/Database.php
 *
 * @author  David Hamp <david.hamp@gmail.com>
 * @license https://github.com/davidhamp/Spiffy/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace SPF\Core;

use \PDO;
use SPF\Exceptions\DatabaseException;

/**
 * PDO Wrapper
 *
 * This will check for database configuration values and create a PDO instance with those settings.
 *
 * @uses SPF\Core\Configuration
 * @uses PDO
 */
class Database
{

    protected $dbConfig;

    /**
     * Constructor
     *
     * Requires the Configuration instance to get database config values.
     *     Example Config values:
     *         databases:
     *                mysql:
     *                   -
     *                       name: 'Resume Service Database'
     *                       host: 'localhost'
     *                       port: '3306'
     *                       username: 'root'
     *                       password: 'qweasd123'
     *                       database: 'ResumeService'
     *                       type: 'master'
     *
     * @param SPF\Core\Configuration $config Configuration class instance.
     *
     * @uses SPF\Core\Configuration
     *
     * @return void
     *
     * @SPF:DmManaged
     * @SPF:DmRequires SPF\Core\Configuration $config
     */
    public function __construct(Configuration $config)
    {
        $this->dbConfig = $config->get('databases');
    }

    /**
     * Returns a PDO connection to the corresponding mysql database defined in the config
     *
     * Inspects the 'database' config values and looks for mysql section.  If mysql is found, it will use all of the
     *     appropriate settings to create a PDO instance.
     *     If no id is provided, it will default to the first db defined.
     *     If no db is defined, or if the config is missing required information this will throw an exception.
     *
     * @param  int $id Database id from the configuration (array index)
     *
     * @throws SPF\Exceptions\DatabaseException
     *
     * @return PDO PDO instance
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