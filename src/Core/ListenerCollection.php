<?php

namespace tantrum\Core;

use tantrum\Exception;

class ListenerCollection 
{
    private static $listeners = array();
    private static $self = null;

    protected final function __construct(){}

    /**
     * Create a singleton instance
     * @return tantrum\Core\ListenerCollection
     */
    public static function init()
    {
        if (null === self::$self) {
            $self = self::$self = new ListenerCollection();
            foreach(self::getDefaultListeners() as $key => $listener) {
                $self::addListener($key, $listener);
            }
        }
        return self::$self;
    }

    /**
     * Add a callback
     * @param string   $name
     * @param callable $callback
     */
    public static function addListener($name, $callback)
    {
        if(!is_callable($callback)) {
            throw new Exception\Exception('Listener '.$name.' is not a valid callback');
        }
        self::$listeners[$name] = $callback;
    }

    /**
     * Call a listener
     * @param  string $name
     * @return mixed
     */
    public static function callListener($name, $args)
    {
        if(!array_key_exists($name, self::$listeners)) {
            return false;
        }
        return call_user_func_array(self::$listeners[$name], $args);
    }

    private static function getDefaultListeners()
    {
        return array(
            'mapColumnName' => function($key){return $key;},
        );
    }
}