<?php

class Bootstrap
{
    
    public static function Init()
    {
        spl_autoload_register('Bootstrap::Autoload');
    }

    public static function Autoload($class)
    {
        var_dump($class);
        $parts = explode('\\', $class);
        if($parts[0] == 'tomcroft' && $parts[1] == 'tantrum') {
            unset($parts[0]);
            unset($parts[1]);
            $className = implode(DIRECTORY_SEPARATOR, $parts);
            $fileAndPath = realpath(str_replace('/', DIRECTORY_SEPARATOR, __DIR__.'/../../src/'.$className.'.php'));
            if(file_exists($fileAndPath)) {
                require($fileAndPath);
            } else {
                throw new \Exception($class.' not found.');
            }
        } elseif($parts[0] == 'tests' && $parts[1] == 'lib') {
            $className = implode(DIRECTORY_SEPARATOR, $parts);
            $fileAndPath = realpath(str_replace('/', DIRECTORY_SEPARATOR, __DIR__.'/../../'.$className.'.php'));
            if(file_exists($fileAndPath)) {
                require($fileAndPath);
            } else {
                throw new \Exception($class.' not found.');
            }
        }
    }
}

Bootstrap::Init();