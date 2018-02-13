<?php

//xdebug : vi "/etc/php/7.1/mods-available/xdebug.ini"
/*
zend_extension=/usr/lib/php/20160303/xdebug.so
xdebug.force_error_reporting=1
xdebug.force_display_errors=1
xdebug.remote_enable=1
xdebug.autostart=1
xdebug.remote_autostart=1
xdebug.remote_connect_back=1
xdebug.remote_port=9999
xdebug.max_nesting_level=512
xdebug.remote_host=192.168.0.157
xdebug.remote_mode=req
xdebug.remote_log=/var/log/xdebug/connection.log
;xdebug.scream=1
xdebug.auto_trace=1
xdebug.idekey=vagrant
*/

//phpinfo();

//------------------------------------------------------------------

class Routes{

    protected static $routes = [
        'home' => '/'
    ];

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
        echo 'Controller::index';
        var_dump($request);
    }

    public function all(Request $request){
        echo 'Controller::all';
        var_dump($request);
    }


}

//------------------------------------------------------------------

class MiddleWare{

    public function __construct(){

    }

    public function auth(Request $request){
        echo 'MiddleWare::auth';
        var_dump($request);
        $request->{'auth'} = true;
        return $request;
    }

    public function process(Request $request){
        echo 'MiddleWare::process';
        var_dump($request);
        $request->{'process'} = true;
        return $request;
    }

    public function check(Request $request){
        echo 'MiddleWare::check';
        var_dump($request);
        $request->{'check'} = true;
        return $request;
    }

    public function verify(Request $request){
        echo 'MiddleWare::verify';
        var_dump($request);
        if($request->get('name') != 'dwizzel'){
            Router::redirect(Routes::get('home'));
        }
        $request->{'verify'} = true;
        return $request;
    }

    public function out(Request $request, Closure $closure){
        echo 'MiddleWare::out';
        var_dump($request);
        return $closure($request);
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
    protected static $func;
    protected static $middleware = [
        'out' => MiddleWare::class
    ];
    
    public function __construct(){
    }

    public static function redirect($url){
        Header('Location: '.$url);
        exit();
    }

    protected static function self($request = false, $func = false){
        self::$request = $request;
        self::$func = $func;
        if (self::$inst === null){
            self::$inst = new Router;
        }
        return self::$inst;
    }

    private function match($url, $path){
        $paths = explode('/', $path);
        $regs = explode('/', $url);
        if(count($paths) != count($regs)){
            return false;
        }
        $request = new Request();
        foreach($paths as $k=>$v){
            if(!isset($regs[$k])){
                return false;
            }   
            $split = false;
            echo 'VALUE: "'.$v.' :: '.$regs[$k].'"'.PHP_EOL;
            if(preg_match('/^(.*)\{([a-zA-Z]+)\}(.*)$/', $regs[$k], $split)){
                //print_r($split);
                echo '/^'.$split[1].'([0-9a-zA-Z]+)'.$split[3].'$/'.PHP_EOL;
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

    private function callClassMethod($args, Request $req){
       list($class, $method) = explode('@', $args);
        if(method_exists($class, $method)){
            //$class::$method($req); //static :: deprecated
            (new $class)->{$method}($req);
        }
    }

    private function callMiddleWare($arr, Request $req){
        foreach($arr as $k=>$v){
            list($class, $method) = explode('@', $v);
            if(method_exists($class, $method)){
                $req = (new $class)->{$method}($req);
                if($req === false){
                    return false;
                }
            }
        }
        return $req;
     }
    
    public static function __callStatic($method, $args){
        if(strtoupper($method) != $_SERVER['REQUEST_METHOD']){
            return;
        }
        $url = parse_url($_SERVER['REQUEST_URI']);
        $path = $url['path'];
        $match = $args[0];
        $func = $args[1];
        $req = (new Router)->match($match, $path);
        if($req){
            $req->data($method);
            if(isset($args[2]) && is_array($args[2])){
                $req = (new Router)->callMiddleWare($args[2], $req);
                if($req === false){
                    return;
                }
            }
            (is_string($func))? (new Router)->callClassMethod($func, $req) : $func($req);
        }
        return;
    }

    public function chain($name){
        (new self::$middleware[$name])->{$name}(self::$request, self::$func);
    }
    
    public function __call($method, $args){
        echo 'dynamic method "'.$method.'" dont exist'.PHP_EOL;
    }

}

//------------------------------------------------------------------

Routes::set('clients', '/clients/');
Routes::set('clients-id', '/clients/{id}/');
Routes::set('clients-account-id', '/clients/{id}/accounts/{accountId}/');

//(new Router)->test();

Router::get('/', 'Controller@index');

Router::get('/clients/', function (Request $request){
    var_dump($request);
}, ['MiddleWare@auth']);

Router::get('/clients/', 'Controller@all', ['MiddleWare@auth']);

Router::get('/clients/{id}/', function(Request $request){
    var_dump($request);
}, ['MiddleWare@check', 'MiddleWare@process']);

Router::get('/clients/{id}/accounts/{accountId}/', function(Request $request){
    var_dump($request);
}, ['MiddleWare@verify']);

Router::get('/clients/{id}/[a-z]{3}-{accountId}-test/', function(Request $request){
    var_dump($request);
});

Router::get('/clients/{id}/{accountId}/', function(Request $request){
    var_dump($request);
});

Router::put('/clients/{id}', function(Request $request){
    var_dump($request);
});

Router::delete('/clients/{id}', function(Request $request){
    var_dump($request);
});

Router::post('/clients/', function(Request $request){
    var_dump($request);
});





