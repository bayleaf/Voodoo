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
 * @name        Env (Environment)
 * @desc        Class for your application's environment
 *
 */

namespace Voodoo\Core;

class Env {

    CONST DEV           = 1;
    CONST STAGING       = 2;    
    CONST PRODUCTION    = 3;

    private static $paths = [];
    private static $env = null;
    
    
    /**
     * Check if the SAPI is CLI or not
     * 
     * @return bool
     */
    public static function isCLI()
    {
        return php_sapi_name()=== "cli";
    }
    
    /**
     * Set the environment we're working on. ie: DEV, PRODUCTION, STAGING
     * 
     * @param int $env
     */
    public static function set($env = self::DEV)
    {
        self::$env = $env;
    }
    
    /**
     * Check if DEV enviroment
     * 
     * @return bool
     */
    public static function isDev()
    {
        return self::$env == self::DEV;
    }
    
    /**
     * Check if PRODUCTION enviroment
     * @return bool
     */    
    public static function isProduction()
    {
        return self::$env == self::PRODUCTION;
    }
    
    /**
     * Check if STAGING enviroment
     * 
     * @return bool
     */    
    public static function isStaging()
    {
        return self::$env == self::STAGING;
    }
    
    /**
     * Get the domain url
     * 
     * @return string
     */
    public static function getUrl()
    {
        $domain = "http";
        $domain .= self::isHttps() ? "s" : "";
        $domain .= "://".self::getHostName();
        
        return $domain;
    }
    
    /**
     * Return the server name
     * 
     * @return string
     */
    public static function getServerName()
    {
        return $_SERVER["SERVER_NAME"];
    }
    
    /**
     * Get the server http host name
     * 
     * @return string
     */
    public static function getHostName()
    {
        return $_SERVER["HTTP_HOST"];
    }

    /**
     * Check if the environment is under HTTPS
     * 
     * @return bool
     */
    public static function isHttps()
    {
        return (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== 'off') 
                || ($_SERVER["SERVER_PORT"] == 443)  ;     
    }
    
    
    /**
     * Return the current PHP version
     * 
     * @return string
     */
    public static function getPhpVersion()
    {
        return \PHP_VERSION;
    } 
    
    
    /**
     * Get the running application's base dir, usually the root where index.php is being run
     * 
     * @return string
     */
    public static function getApplicationBaseDir()
    {
        return str_replace("\\","/",pathinfo($_SERVER["SCRIPT_NAME"],PATHINFO_DIRNAME));
    }
    
    /**
     * Root of Voodoo directory
     *
     * @return string
     */
    public static function getRootDir()
    {
        return dirname(dirname(__DIR__));
    }   
    
    public static function getVoodooistPath()
    {
        return dirname(__DIR__)."/Voodooist";
    }
    
    /**
     * Set the front controller path, where the index will be created
     * 
     * @param string $appDir
     */
    public static function setFrontControllerPath($rootDir){
        self::$paths["FrontController"] = $rootDir;
    }
    /**
     * Return the front controller path
     * 
     * @return string
     */
    public static function getFrontControllerPath()
    {
        return self::$paths["FrontController"];
    }    
    
    /**
     * Set the app dir
     * 
     * @param string $appDir
     */
    public static function setAppPath($rootDir)
    {
        self::$paths["App"] = $rootDir."/App";
        if (! self::getConfigPath()) { // by default
            self::setConfigPath(self::getAppPath()."/_config");    
        }
    }
    
    /**
     * Get the app dir
     * 
     * @return string
     */
    public static function getAppPath()
    {
        return self::$paths["App"];
    }
    
    /**
     * set the config dir
     * 
     * @param string $privateDir
     */
    public static function setConfigPath($path)
    {
        self::$paths["config"] = $path;
    }
    
    /**
     * Get the config
     * 
     * @return string
     */
    public static function getConfigPath()
    {
        return self::$paths["config"];
    }

    /**
     * Set the public assets
     * 
     * @param string $rootDir
     */
    public static function setPublicAssetsPath($rootDir)
    {
        self::$paths["publicAssets"] = $rootDir."/assets";
    }
    
    /**
     * Get the public assets
     * 
     * @return string
     */
    public static function getPublicAssetsPath()
    {
        return self::$paths["publicAssets"];
    }
    
    
    /**
     * To start a session if it's not active
     * Once set, you can use $_SESSION to work with your data
     */
    public static function startSession()
    {
        if( session_status() !=  PHP_SESSION_ACTIVE) {
            session_start();
        }        
    }
}

