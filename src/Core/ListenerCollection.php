<?php

namespace tantrum\Core;

use tantrum\Exception;

class ListenerCollection 
{
    private static $listeners = array();
    private static $self = null;

    protected final function __construct(){
        $this->addDefaultListeners();
    }

    /**
     * Create a singleton instance
     * @return tantrum\Core\ListenerCollection
     */
    public static function init()
    {
        if (null === self::$self) {
            self::$self = new ListenerCollection();
        }
        return self::$self;
    }

    /**
     * Add a callback
     * @param string   $name
     * @param callable $callback
     */
    public static function addListener($name, Callable $callback)
    {
        if(!is_callable($callback)) {
            throw new \Exception('Listener '.$name.' is not a valid callback');
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

    private static function addDefaultListeners()
    {
        self::addListener('mapColumnName', function($key){return $key;});
    }
}