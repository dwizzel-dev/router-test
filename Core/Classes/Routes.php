<?php

namespace Core\Classes;

//------------------------------------------------------------------

class Routes{

    /*
    // php 7
    protected static $routes = [
        'home' => '/'
    ];
    */
    protected static $routes = array(
        'home' => '/'
    );

    private function get($key){
        if(isset(static::$routes[$key])){
            return static::$routes[$key];
        }
        return false;
    }

    private function set($key, $value){
        static::$routes[$key] = $value;
    }

    public static function __callStatic($method, $args){
        switch($method){
            case 'get':
                return (new Routes)->get($args[0]);
            case 'set':
                return (new Routes)->set($args[0], $args[1]);
            default:
                break;        
        }
    }
}