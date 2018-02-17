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

    public static function get($key){
        if(isset(static::$routes[$key])){
            return self::$routes[$key];
        }
        return false;
    }

    public static function set($key, $value){
        self::$routes[$key] = $value;
    }

}