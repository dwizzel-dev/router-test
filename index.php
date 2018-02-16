<?php

namespace Core\Classes;

//xdebug_get_code_coverage();

//phpinfo();

//throw new \Exception("unable display exception");

require_once('define.php');

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






