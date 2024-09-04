<?php

namespace Spaf;

use Spaf\Core\Router;
use Spaf\Core\Database;
use Spaf\Core\Request;
use Spaf\Core\Response;

use \PDO;

class App{

    private Request $request;
    private Response $response;
    public Router $routes;
    
    private $database;
    private $endpoint;

    public String $appNamespace = "App\\";

    public function __construct(String $namespace = '')
    {
        // cargar rutas
        $this->routes = new Router();

        if($namespace) {
            $this->appNamespace = $namespace;
        }
    }

    public function run()
    {
        $this->initRequest();

        if( $this->validRequest() ) {
            $this->executeRequest();
        } else {
            $this->response = new Response(Response::HTTP_NOT_FOUND);
        }

        $this->sendResponse();
    }

    protected function initRequest() {
        $this->request = new Request();
    }

    private function validRequest()
    {
        $this->endpoint = $this->routes->resolve($this->request->method, $this->request->uri);
        if(!is_null($this->endpoint)) {
            return true;
        }
        return false;
    }

    private function executeRequest()
    {
        if($this->endpoint) {

            if(!$this->preProccessRequest()){
                return;
            }

            $response = $this->processRequest();

            if(!is_object($response) || !is_a($response, 'Spaf\Core\Response')) {
                $response = new Response(Response::HTTP_OK);
            }

            $this->response = $response;

            $this->postProcessRequest();
        }
    }

    private function preProccessRequest()
    {
        if($this->endpoint['prefilters']) {
            foreach ($this->endpoint['prefilters'] as $filter) {

                $params = [];
                if(strpos($filter,'[')) {
                    [$filter, $params] = explode('[', $filter);
                    $params = str_replace(']','',$params);
                    $params = explode(',',$params);
                }

                $class = $this->appNamespace . "Filters\\" . $filter;
                $object = new $class();
                $response = $object->before($this->request, $params);
                if(is_object($response) && is_a($response, 'Spaf\Core\Response')) {
                    $this->response = $response;
                    return false; // break the execution
                } elseif ($response !== true) {
                    $this->response = new Response(Response::HTTP_NOT_IMPLEMENTED);
                    return false; // break the execution
                }

            }
        }
        return true;
    }

    private function processRequest()
    {
        $class = $this->appNamespace . "Services\\" . $this->endpoint['controller'];
        
        $object = new $class($this->request);

        if( $object->requireDB() ) {
            $this->connectDB();
            $object->setDB($this->database);
        }

        $function = $this->endpoint['function'];

        return $object->{$function}();
    }

    private function postProcessRequest()
    {
        if($this->endpoint['postfilters']) {
            foreach ($this->endpoint['postfilters'] as $filter) {

                $params = [];
                if(strpos($filter,'[')) {
                    [$filter, $params] = explode('[', $filter);
                    $params = str_replace(']','',$params);
                    $params = explode(',',$params);
                }

                $class = $this->appNamespace . "Filters\\" . $filter;
                $object = new $class();
                $response = $object->after($this->request, $this->response, $params);
                if(is_object($response) && is_a($response, 'Spaf\Core\Response')) {
                    $this->response = $response;
                    return false; // break the execution
                } elseif ($response !== true) {
                    return false; // break the execution
                }
            }
        }
        return true;
    }

    public function connectDB(String $host='', String $name='', String $user='', String $pass='', Array $options=[])
    {
        if($this->database) {
            return true;
        }

        if(!$host) {
            $host = env('db_host');
            $user = env('db_user','');
            $name = env('db_name','');
            $pass = env('db_pass','');
        }

        if(!$host) {
            die("Missconfiguration");
        }

        $dsn = "mysql:host={$host};dbname={$name}";

        $defopt = [
            PDO::ATTR_CASE => PDO::CASE_LOWER,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        $options = array_merge($defopt, $options);

        $this->database = new \PDO(
            $dsn,
            $user,
            $pass,
            $options
        );
    }

    public function sendResponse()
    {
        if(!$this->response) {
            $this->response = new \Response(\Response::HTTP_CONFLICT);
        }
        $this->response->printHeaders();
        $this->response->printBody();
    }
}
