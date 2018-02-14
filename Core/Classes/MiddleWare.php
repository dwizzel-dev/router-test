<?php

namespace Core\Classes;

//------------------------------------------------------------------

class MiddleWare{

    public function __construct(){

    }

    public function auth(Request $request){
        Debug::show(__METHOD__);
        var_dump($request);
        $request->{'auth'} = true;
        return $request;
    }

    public function process(Request $request){
        Debug::show(__METHOD__);
        var_dump($request);
        $request->{'process'} = true;
        return $request;
    }

    public function check(Request $request){
        Debug::show(__METHOD__);
        var_dump($request);
        $request->{'check'} = true;
        return $request;
    }

    public function verify(Request $request){
        Debug::show(__METHOD__);
        var_dump($request);
        if($request->get('name') != 'dwizzel'){
            Router::redirect(Routes::get('home'));
        }
        $request->{'verify'} = true;
        return $request;
    }

    public function test(){
        Debug::show(__METHOD__);
    }

}