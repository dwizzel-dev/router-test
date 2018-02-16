<?php
/*
@auth: dwizzel
@date: 13-02-2018
@info: basic define file for path and constatn all around the globe
*/
//basic define
define('VERSION', '1.0.0');
define('ENV', 'env');

spl_autoload_register(function ($class_name) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class_name).'.php';
    (file_exists($file))? require $file : exit('error loading: '.$file);
});
