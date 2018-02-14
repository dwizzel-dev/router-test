<?php

namespace Core\Classes;

//------------------------------------------------------------------

class Json{
    
	static public function encode($data){
		if (function_exists('json_encode')) {
			$data = json_encode($data, JSON_UNESCAPED_UNICODE);
			if(json_last_error() != JSON_ERROR_NONE){
				return json_last_error();
			}
			return $data;
		}
		return false;
	}
	static public function decode($json, $assoc = false){
		if (function_exists('json_decode')) {
			$data = json_decode($json, true);
			if(json_last_error() != JSON_ERROR_NONE){
				return json_last_error();
			}
			return $data;
		}
		return false;
	}
}