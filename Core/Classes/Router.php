<?php

namespace Core\Classes;

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
        $regs = array_filter(explode('/', $url), array('self', 'cleanUrl'));
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

    public function before($name = null){

    }

    
    public function __call($method, Array $args){
        Debug::show(__METHOD__);
        Debug::show('dynamic method "'.$method.'" dont exist');
    }

}