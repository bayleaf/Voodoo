<?php
/**
 * -----------------------------------------------------------------------------
 * VoodooPHP
 * -----------------------------------------------------------------------------
 * @author      Mardix (http://twitter.com/mardix)
 * @github      https://github.com/VoodooPHP/Voodoo
 * @package     VoodooPHP
 *
 * @copyright   (c) 2012 Mardix (http://github.com/mardix)
 * @license     MIT
 * -----------------------------------------------------------------------------
 *
 * @name        Model\ConnectionManager
 * @desc        ConnectionManager creates a single connection to your database
 *              It statically hold each connection per aliasName.
 *              For MySQL, PGSQL, SQlite it will return PDO
 *              For DSN type, it return the class provided at dsnDependency
 *
 */

namespace Voodoo\Core\Model;

use Voodoo\Core,
    PDO,
    ReflectionClass;

class ConnectionManager
{
    /**
     * Holds all the connection
     * @var type
     */
    private static $dbConnections = [];

    /**
     * To establish the connection based on the DBAlias provided in the DB.ini
     * It will only connects to the db once, then
     *
     * @param  string               $dbAlias
     * @return \PDO|\Redisent\Redis
     * @throws Core\Exception
     */
    public static function connect($dbAlias)
    {
        if (!isset(self::$dbConnections[$dbAlias])) {

            $db = Core\Config::DB()->get($dbAlias);

            if(!is_array($db)){
                throw new Core\Exception("Database Alias: {$dbAlias} config doesn't exist.");
            }
            $dbType = strtolower($db["type"]);
            if (preg_match("/mysql|pgsql|sqlite|dsn/i", $dbType)) {
                switch ($dbType) {
                    /**
                     * To manage RDMS connection using PDO
                     * Also creates EXCEPTION as error mode
                     * 
                     * @return PDO
                     */
                    case "mysql":
                    case "pgsql":
                    case "sqlite":
                        if ($dbType == "sqlite"){ // Requires: dbFile
                            $PDO = new PDO("sqlite:{$db["dbFile"]}");
                        } else {
                            $port = (isset($db["port"]) && $db["port"]) ? ";port={$db["port"]}" : "";
                            $dsn = "{$dbType}:host={$db["host"]};dbname={$db["dbName"]}{$port}";
                            $PDO = new PDO($dsn, $db["user"], $db["password"]);
                        }
                        $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        //@return PDO
                        self::$dbConnections[$dbAlias] = $PDO;
                        break;
                    
                    /**
                     * DSN (Data Source Name)
                     * To manage other connections such as MongoDB, Redis etc
                     * It requires the 'dsn' and class to create the instance
                     * For MongoDB
                     *      dsn = 'mongodb://localhost:27017'
                     *      dsnDependency = '\MongoClient'
                     * 
                     * For Redis
                     *      dsn = '1.0.0.1:6379'
                     *      dsnDependency = '\Redisent\Redis'
                     * 
                     * @return object dsnDependency
                     */
                    case "dsn":
                        $dsnDependency = new ReflectionClass($db["dsnDependency"]);  
                        //@return dsnDependency
                        self::$dbConnections[$dbAlias] = $dsnDependency->newInstance($db["dsn"]);                        
                        break;
                }
            } else {
                throw new Core\Exception("Invalid type for Alias: '{$dbAlias}'. Type: {$db["type"]} was provided");
            }
        }
        return self::$dbConnections[$dbAlias];
    }
}
