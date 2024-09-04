<?php

namespace Spaf\Core;

class Request {
    public $uri;
    public $method;

    public $query_args;
    public $raw_input_stream;
    public $body;

    public $args;

    public $headers;

    public function __construct()
    {
        $this->initMethod();
        $this->initUri();
        $this->populateHeaders();
        $this->populateArgs();
    }

    private function initMethod()
    {
        $this->method  = strtolower(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'get');
    }

    private function initUri()
    {
        $this->uri = $_SERVER['REQUEST_URI'];
    }

    private function populateHeaders()
    {
        //$contentType = $_SERVER['CONTENT_TYPE'] ?? getenv('CONTENT_TYPE');
        // if (! empty($contentType)) {
        //     $this->setHeader('Content-Type', $contentType);
        // }
        // unset($contentType);

        foreach (array_keys($_SERVER) as $key) {
            if (sscanf($key, 'HTTP_%s', $header) === 1) {
                $header = str_replace('_', '-', strtolower($header));
                $this->setHeader($header, $_SERVER[$key]);
            }
        }
    }

    private function setHeader($headerName, $headerValue)
    {
        if (!isset($this->headers[$headerName])) {
            $this->headers[$headerName] = [];
        }

        if (! is_array($headerValue)) {
            $headerValue = [$headerValue];
        }

        foreach ($headerValue as $v) {
            $this->headers[$headerName][] = $v;
        }
    }

    public function header($name)
    {
        $name = strtolower($name);
        if(!array_key_exists($name, $this->headers)) {
            return null;
        }
        $header = $this->headers[$name];
        if(count($header) == 1) {
            return $header[0];
        }
        return $header;
    }

    private function populateArgs()
    {
        $this->query_args = $_GET;
        $this->raw_input_stream = file_get_contents('php://input');
        $this->body = json_decode($this->raw_input_stream, true);

        switch ($this->method) {
            case 'get':
                    $this->args = $this->query_args;
                break;
            case 'post':
            case 'put':
            case 'delete':
                    parse_str($this->raw_input_stream, $args);
                    $this->args = is_array($args) ? $args : array();
                break;
            default:
                # code...
                break;
        }

        if( is_array($this->body) ){
            $this->args = array_merge($this->args, $this->body);
        }
    }


}