<?php

namespace Core\Classes;

//------------------------------------------------------------------

class OuterWare{    

    public function in(Response $response){
        Debug::show(__METHOD__);
        $response->set('in', true);
        return $response;
    }

    public function middle(Response $response){
        Debug::show(__METHOD__);
        $response->set('middle', true);
        return $response;
    }

    public function out(Response $response){
        Debug::show(__METHOD__);
        $response->set('out', true);
        return $response;
    }

    


}