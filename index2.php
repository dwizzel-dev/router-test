<?php

namespace Dwizzel\Routing;

error_reporting(E_ERROR);
Header('Content-type: text/html');

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

    public function in(Request $request){
        Debug::show(__METHOD__);
        var_dump($request);
        $request->{'in'} = true;
    }

    public function middle(Request $request){
        Debug::show(__METHOD__);
        var_dump($request);
        $request->{'middle'} = true;
    }

    public function out(Request $request){
        Debug::show(__METHOD__);
        var_dump($request);
        $request->{'out'} = true;
    }

    


}

//------------------------------------------------------------------

Class Request{

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

    public function set($key){
        $this->_data = $value;
    }

    public function get($key){
        if(isset($this->_data[$key])){
            return $this->_data[$key];
        }
        return false;
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

Class Router{

    protected static $inst;
    protected static $request;
    /*
    // php 7
    protected static $middleware = [
        'out' => MiddleWare::class
    ];
    */
    // php 5.4
    protected static $outerware = array(
        'in' => 'OuterWare',
        'middle' => 'OuterWare',
        'out' => 'OuterWare'
    );

    public function __construct(){
        Debug::show(__METHOD__);
    }

    public static function redirect($url){
        Header('Location: '.$url);
        exit();
    }

    protected static function self($request = null){
        self::$request = $request;
        if (self::$inst === null){
            self::$inst = new Router;
        }
        return self::$inst;
    }

    private function cleanUrl($value){
        return ($value != '');
    }

    private function match($url, $path){
        $paths = array_filter(explode('/', $path), 'self::cleanUrl');
        $regs = array_filter(explode('/', $url), array(self, cleanUrl));
        if(count($paths) != count($regs)){
            return false;
        }
        $request = new Request();
        var_dump(array($paths, $regs));    
        foreach($paths as $k=>$v){
            if(!isset($regs[$k])){
                return false;
            }   
            $split = false;
            //Debug::show('VALUE: "'.$v.'" :: "'.$regs[$k].'"');
            if(preg_match('/^(.*)\{([a-zA-Z]+)\}(.*)$/', $regs[$k], $split)){
                //print_r($split);
                //Debug::show('REGEX: "/^'.$split[1].'([0-9a-zA-Z]+)'.$split[3].'$/"');
                if(preg_match('/^'.$split[1].'([0-9a-zA-Z]+)'.$split[3].'$/', $v, $match)){
                    $request->{$split[2]} = $match[1];
                }else{
                    return false;    
                }
            }else if($regs[$k] != $v){
                return false;
            }
        }
        return $request;
    }

    private function callClassMethod($args){
        list($class, $method) = explode('@', $args);
        $class = __NAMESPACE__.'\\'.$class; 
        if(method_exists($class, $method)){
            //$class::$method($req); //static :: deprecated
            (new $class)->{$method}(self::$request);
        }
    }

    private function callMiddleWare($arr){
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
        return self::$request;
     }
    
    public static function __callStatic($method, $args){
        Debug::show(__METHOD__);
        if(self::$request != null){
            return;
        }
        //var_dump($args);
        if(strtoupper($method) != $_SERVER['REQUEST_METHOD']){
            return;
        }
        //(new Router)->self();
        $url = parse_url($_SERVER['REQUEST_URI']);
        $path = $url['path'];
        $match = $args[0];
        $func = $args[1];
        self::self(self::match($match, $path));
        if(self::$request){
            self::$request->data($method);
            if(isset($args[2]) && is_array($args[2])){
                self::$request = self::callMiddleWare($args[2]);
                if(self::$request === false){
                    //return self::$inst;
                    //return new static;
                    return;
                }
            }
            (is_string($func))? self::callClassMethod($func) : $func(self::$request);
        }
        //return self::$inst;
        //return new static;
        return;
    }

    public function chain($name){
        if(is_array($name)){
            foreach($name as $v){
                $class = __NAMESPACE__.'\\'.$outerware[$v];
                (new self::$class)->{$v}(self::$request);
            }
        }else{
            $class = __NAMESPACE__.'\\'.$outerware[$name];
            (new self::$class)->{$name}(self::$request);
        }
        return self::$inst;
    }
    
    public function __call($method, Array $args){
        Debug::show('dynamic method "'.$method.'" dont exist');
    }

}

//------------------------------------------------------------------

Routes::set('clients', '/clients/');
Routes::set('clients-0', '/clients/');
Routes::set('test-0', '/test-0/');
Routes::set('test-1', '/test-1/');
Routes::set('test-2', '/test-2/');
Routes::set('clients-id', '/clients/{id}/');
Routes::set('clients-account-id', '/clients/{id}/accounts/{accountId}/');

//(new Router)->test();

Router::get('/', 'Controller@index');

Router::get('/clients/', 'Controller@all', 
        ['MiddleWare@auth','MiddleWare@check', 'MiddleWare@process']);

Router::get('/clients/{id}/', function(Request $request){
    Debug::show(__METHOD__);
    var_dump($request);
}, ['MiddleWare@check', 'MiddleWare@process']);

Router::get('/clients/{id}/accounts/{accountId}/', function(Request $request){
    Debug::show(__METHOD__);
    var_dump($request);
}, ['MiddleWare@verify']);

Router::get('/clients/{id}/[a-z]{3}-{accountId}-test/', function(Request $request){
    Debug::show(__METHOD__);
    var_dump($request);
});

Router::get('/clients/{id}/{accountId}/', function(Request $request){
    Debug::show(__METHOD__);
    var_dump($request);
});


// -- testing -------------------------------



Router::get('/test-0/', function(Request $request){
    Debug::show(__METHOD__);
    var_dump($request);
});

Router::get('/test-1/', function(Request $request){
    Debug::show(__METHOD__);
    var_dump($request);
}, ['MiddleWare@auth']);

Router::get('/test-2/', 'Controller@index', ['MiddleWare@auth']);
    
Router::get('/clients-0/', function (Request $request){
    Debug::show(__METHOD__);
    var_dump($request);
}, ['MiddleWare@auth']);





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






