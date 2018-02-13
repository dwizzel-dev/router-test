<?php

namespace Dwizzel\Routing;

error_reporting(E_ALL);
//Header('Content-type: text/html');

//------------------------------------------------------------------

class Debug{

    public static function show($str){
        echo '<pre>'.$str.'</pre>';
    }

}


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

    private function get($key){
        if(isset(static::$routes[$key])){
            return static::$routes[$key];
        }
        return false;
    }

    private function set($key, $value){
        static::$routes[$key] = $value;
    }

    public static function __callStatic($method, $args){
        switch($method){
            case 'get':
                return (new Routes)->get($args[0]);
            case 'set':
                return (new Routes)->set($args[0], $args[1]);
            default:
                break;        
        }
    }
}

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

//------------------------------------------------------------------

class Router{

    protected static $inst = null;
    protected static $request = null;
    protected static $response = null;

    public function __construct(){
        Debug::show(__METHOD__);
    }

    public static function redirect($url){
        //Debug::show(__METHOD__);
        Header('Location: '.$url);
        exit();
    }

    protected static function self($request = null){
        //Debug::show(__METHOD__);
        self::$request = $request;
        if (self::$inst === null){
            self::$inst = new Router;
        }
        return self::$inst;
    }

    private function cleanUrl($value){
        //Debug::show(__METHOD__);
        return ($value != '');
    }

    private function match($url, $path){
        //Debug::show(__METHOD__);
        $paths = array_filter(explode('/', $path), 'self::cleanUrl');
        $regs = array_filter(explode('/', $url), 'self::cleanUrl');
        if(count($paths) != count($regs)){
            return;
        }
        $request = new Request;
        var_dump(array($paths, $regs));    
        foreach($paths as $k=>$v){
            if(!isset($regs[$k])){
                return;
            }   
            $split = false;
            //Debug::show('VALUE: "'.$v.'" :: "'.$regs[$k].'"');
            if(preg_match('/^(.*)\{([a-zA-Z]+)\}(.*)$/', $regs[$k], $split)){
                //Debug::show('REGEX: "/^'.$split[1].'([0-9a-zA-Z]+)'.$split[3].'$/"');
                if(preg_match('/^'.$split[1].'([0-9a-zA-Z]+)'.$split[3].'$/', $v, $match)){
                    $request->{$split[2]} = $match[1];
                }else{
                    return;    
                }
            }else if($regs[$k] != $v){
                return;
            }
        }
        return $request;
    }

    private function callClassMethod($args){
        Debug::show(__METHOD__);
        list($class, $method) = explode('@', $args);
        $class = __NAMESPACE__.'\\'.$class; 
        if(method_exists($class, $method)){
            //$class::$method($req); //static :: deprecated
            (new $class)->{$method}(self::$request);
        }
    }

    private function callMiddleWare($arr){
        Debug::show(__METHOD__);
        foreach($arr as $k=>$v){
            list($class, $method) = explode('@', $v);
            $class = __NAMESPACE__.'\\'.$class; 
            if(method_exists($class, $method)){
                self::$request = (new $class)->{$method}(self::$request);
                if(self::$request === false){
                    return false;
                }
            }
        }
        return true;
     }
    
    public static function __callStatic($method, $args){
        //Debug::show(__METHOD__);
        if(strtoupper($method) != $_SERVER['REQUEST_METHOD']){
            return self::$inst;
        }
        if(!is_null(self::$request)){
            return self::$inst;
        }
        //(new Router)->self();
        $url = parse_url($_SERVER['REQUEST_URI']);
        $path = $url['path'];
        $match = $args[0];
        $func = $args[1];
        self::self();
        self::$request = self::$inst->match($match, $path);
        if(self::$request){
            self::$response = new Response;
            self::$request->data($method);
            if(isset($args[2]) && is_array($args[2])){
                if(!self::$inst->callMiddleWare($args[2])){
                    return self::$inst;
                }
            }
            (is_string($func))? self::$inst->callClassMethod($func) : $func(self::$request);
        }
        return self::$inst;
    }

    private function chain($name){
        Debug::show(__METHOD__);
        if(is_array($name)){
            foreach($name as $v){
                list($class, $method) = explode('@', $v);
                $class = __NAMESPACE__.'\\'.$class; 
                self::$response = (new $class)->{$method}(self::$response);
            }
        }else{
            list($class, $method) = explode('@', $name);
            $class = __NAMESPACE__.'\\'.$class; 
            self::$response = (new $class)->{$method}(self::$response);
        }
        return self::$response;
    }

    public function end($name = null){
        //Debug::show(__METHOD__);
        if(is_null(self::$response)){
            return self::$inst;
        }
        if($name !== null){
            self::$inst->chain($name);
        }
        self::$response->set('done', 'OK');
        self::$response->sets(self::$request->getData());
        self::$response->sendHeader(true);
		echo self::$response->output();
        exit();
    }

    
    public function __call($method, Array $args){
        Debug::show(__METHOD__);
        Debug::show('dynamic method "'.$method.'" dont exist');
    }

}

//------------------------------------------------------------------

Routes::set('clients', '/clients/');
Routes::set('clients-id', '/clients/{id}/');
Routes::set('clients-account-id', '/clients/{id}/accounts/{accountId}/');

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
}, ['MiddleWare@verify'])->end();

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






