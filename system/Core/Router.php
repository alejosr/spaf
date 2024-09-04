<?php

namespace Spaf\Core;

class Router {

    private $get = [];
    private $post = [];
    private $delete = [];
    private $put = [];

    public function add(string $method, $path, $controller)
    {
        $method = strtolower($method);
        if($this->validMethod($method)) {
            $this->$method[$path] = $controller;
        }
    }

    public function get($path, $controller)
    {
        $this->add('get', $path, $controller);
    }

    public function post($path, $controller)
    {
        $this->add('post', $path, $controller);
    }

    public function delete($path, $controller)
    {
        $this->add('delete', $path, $controller);
    }

    public function put($path, $controller)
    {
        $this->add('put', $path, $controller);
    }

    public function resolve($method, $path)
    {
        $method = strtolower($method);
        if(!$this->validMethod($method)) {
            return null;
        }

        $resolve = [
            'prefilters' => [],
            'controller' => '',
            'function' => '',
            'postfilters' => []
        ];

        if(isset($this->$method[$path])){
            $function = $this->$method[$path];
            
            $explode = array_map('trim', explode('|', $function));
            $pre = true;
            foreach ($explode as $value) {
                if(strpos($value, '::') !== false) {
                    $pre = false;
                    $broken = explode(":", $value);
                    $resolve['function'] = array_pop($broken);
                    $resolve['controller'] = $broken[0];
                } else {
                    if($pre) {
                        $resolve['prefilters'][] = $value;
                    } else {
                        $resolve['postfilters'][] = $value;
                    }
                }
            }

            return $resolve;
        }

        return null;
    }

    private function validMethod($method)
    {
        return in_array($method, ['get','post','delete','put']);
    }
}