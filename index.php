<?php

use Core\Classes\Router;
use Core\Classes\Debug;
use Core\Classes\Request;

require_once('define.php');

require_once(CLASS_PATH.'Router.php');
require_once(CLASS_PATH.'Debug.php');

//(new Router)->test();

Router::get('/', 'Controller@index')->end();

Router::get('/clients/', 'Controller@all', ['MiddleWare@check', 'MiddleWare@process'])
    ->end(['OuterWare@in', 'OuterWare@middle', 'OuterWare@out']);

Router::get('/clients/{id}/', function(Request $request){
    Debug::show(__METHOD__);
    var_dump($request);
}, ['MiddleWare@auth', 'MiddleWare@check', 'MiddleWare@process'])->end('OuterWare@out');

Router::get('/clients/{id}/accounts/{accountId}/', function(Request $request){
    Debug::show(__METHOD__);
    var_dump($request);
}, [
    'MiddleWare@verify',
    'MiddleWare@auth', 
    'MiddleWare@check', 
    'MiddleWare@process'
    ])->end(
        [
        'OuterWare@in', 
        'OuterWare@middle', 
        'OuterWare@out'
        ]
    );

Router::get('/clients/{id}/[a-z]{3}-{accountId}-test/', function(Request $request){
    Debug::show(__METHOD__);
    var_dump($request);
})->end();

Router::get('/clients/{id}/{accountId}/', function(Request $request){
    Debug::show(__METHOD__);
    var_dump($request);
})->end();



// - get, update, delete -----------------------------


Router::put('/clients/{id}', function(Request $request){
    Debug::show(__METHOD__);
    var_dump($request);
});

Router::delete('/clients/{id}', function(Request $request){
    Debug::show(__METHOD__);
    var_dump($request);
});

Router::post('/clients/', function(Request $request){
    Debug::show(__METHOD__);
    var_dump($request);
});






