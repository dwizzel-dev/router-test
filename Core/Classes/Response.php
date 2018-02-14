<?php

namespace Core\Classes;

//------------------------------------------------------------------

class Response{

    private $_data = array();
    private $_headers = array();
    private $_errors = array();

    public function __construct(){
        
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

    public function sets($values){
		if(is_array($values)){
			$this->_data = $this->recursiveSet($this->_data, $values);
			return true;	
		}
		return false;
    }
    
    private function recursiveSet($arr, $values){
		if(is_array($values)){
			foreach($values as $k=>$v){	
				if(is_array($v)){
					$arr[$k] = array();
					$this->recursiveSet($arr[$k], $v);
				}else{
					$arr[$k] = $v;
				}
			}
        }
        return $arr;
    }
	
	public function clear($bErrors = false) {
        $this->_data = array();
        if($bErrors){
            $this->_errors = array();
        }
	}
	
	public function addError($errmsg, $errno = 0) {
		array_push($this->_errors, array(
			'code' => $errno,
			'message' => $errmsg
		));
    }
    
    public function sendHeader($html = false) {
		if(!headers_sent()) {
            $this->addHeader('Cache-Control:no-cache, private');
            if($html){
                $this->addHeader('Content-Type: text/html; charset=utf-8');
            }else{
                $this->addHeader('Content-Type: application/json; charset=utf-8');
            }
			foreach ($this->_headers as $header) {
				header($header, true);
			}
		}
	}

    public function addHeader($header) {
		$this->_headers[] = $header;
	}

    public function output(){
        if(count($this->_errors)){
			$this->addHeader('HTTP/1.0 400 Bad Request');
			$this->_data = $this->_errors;
		}
		$encoded = Json::encode($this->_data);
		if($encoded === false || is_numeric($encoded)){ 
            $this->clear(true);
            $this->addError('json error: '.$encoded);
            $this->output();
           	return false;
		}
        return $encoded;
    }

}