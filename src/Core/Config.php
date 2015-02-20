<?php

namespace tantrum\Core;

use tantrum\Database,
    tantrum\Exception;

class Config
{
    private static $self = null;
    private static $configs = array(
        'databases' => array()
    );

    protected final function __construct(){}

    /**
     * Create a singleton instance
     * @return tantrum\Core\Config
     */
    public static function init()
    {
        if (null === self::$self) {
           self::$self = new Config();
        }
        return self::$self;
    }

    public static function setDatabase($driver, $host, $schema, $user, $password, $isMaster = true)
    {
        Database\Manager::isSupported($driver);
        $key = sprintf('%s-%s', $driver, $isMaster);
        self::$configs['databases'][$key] = array(
            'host'     => $host,
            'schema'   => $schema,
            'user'     => $user,
            'password' => $password,
        );
    }

    public static function getDatabase($driver, $getMaster = true)
    {
        $key = sprintf('%s-%s', $driver, $getMaster);
        if(!array_key_exists($key, self::$configs['databases'])) {
            throw new Exception\Exception($driver.' does not have any config settings');
        }

        return self::$configs['databases'][$key];
    }
}