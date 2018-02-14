<?php

namespace Core\Classes;

//------------------------------------------------------------------

class Request{

    private $_data = array();
    private $_method;        

    public function __construct(){
        
    }

    public function __set($key, $value){
        $this->{$key} = $value;
    }

    public function __get($key){
        if(isset($this->{$key})){
            return $this->{$key};
        }
        return false;
    }

    public function set($key, $value){
        $this->_data[$key] = $value;
    }

    public function get($key){
        if(isset($this->_data[$key])){
            return $this->_data[$key];
        }
        return false;
    }

    public function getData(){
        return $this->_data;
    }

    public function data($method = 'get'){
        $this->_method = $method;
        foreach($_GET as $k=>$v){
            $this->_data[$k] = $v;
        }
        switch($method){
            case 'put':
                parse_str(file_get_contents('php://input'), $_PUT);
                foreach($_PUT as $k=>$v){
                    $this->_data[$k] = $v;
                }       
                break;    
            case 'post':
                foreach($_POST as $k=>$v){
                    $this->_data[$k] = $v;
                }
                break; 
            default:
                break; 
        }
    }

}