<?php

namespace Core\Classes;

//------------------------------------------------------------------

class Controller{

    public function __construct(){

    }

    public function index(Request $request){
        Debug::show(__METHOD__);
        var_dump($request);
    }

    public function all(Request $request){
        Debug::show(__METHOD__);
        var_dump($request);
    }


}