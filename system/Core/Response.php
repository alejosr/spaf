<?php

namespace Spaf\Core;

use Spaf\Core\Http;

class Response extends Http{

    public int $code;
    public string $httpMessage;
    public array $headers = [];
    public mixed $body;

    public function __construct(mixed $code, mixed $body = null)
    {
        $this->setCode(intval($code));

        if($body){
            $this->setBody($body);
        } else {
            $this->body = '';
        }

        // Por el momento sólo soporta tipo json
        // se toma de una variable de entorno pensando en un futuro soportar otros formatos
        $type = env('response_type', 'json');
        
        // Default headers
        $this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->setHeader('Cache-Control', 'post-check=0, pre-check=0', false);
        $this->setHeader('Content-Type', "application/{$type}; charset=UTF-8");
        $this->setHeader('Pragma', 'no-cache');
    }

    public function setCode(int $code)
    {
        $this->code = $code;
        $this->httpMessage = $this->httpMessage($code);
    }

    public function setBody(mixed $body)
    {
        $this->body = $body;
    }

    public function setHeader($name, $value, $replace=true)
    {
        if($replace) {
            foreach ($this->headers as $key => $header) {
                if(\strtolower($header[0]) == \strtolower($name)){
                    unset($this->headers[$key]);
                }
            }
        }
        $this->headers[] = [$name, $value, $replace];
    }

    public function printHeaders()
    {
        header($_SERVER["SERVER_PROTOCOL"] . ' ' . $this->code . ' ' . $this->httpMessage);
        foreach ($this->headers as $header) {
            header("{$header[0]}: $header[1]", $header[2]);
        }
    }

    public function printBody()
    {
        if(!is_null($this->body)){
            if(is_array($this->body) || is_object($this->body)){
                $body = json_encode($this->body);
            } else {
                json_decode($this->body);
                $body = json_last_error() === JSON_ERROR_NONE ? $this->body : '{}';
            }
            echo $body;
        }
    }

}