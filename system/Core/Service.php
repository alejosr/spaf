<?php

namespace Spaf\Core;

use Spaf\Core\Request;
use Spaf\Core\Validater;
use Spaf\Core\Response;
use \PDO;

class Service{
    protected Request $request;
    protected Validater $validater;
    protected PDO $db;
    protected bool $requireDB = false;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->validater = new Validater;
    }

    public function requireDB()
    {
        return $this->requireDB ?? false;
    }

    public function setDB(\PDO &$db)
    {
        $this->db = $db;
    }

    public function input($name)
    {
        if(isset($this->request->args[$name])) {
            return $this->request->args[$name];
        }
        return null;
    }

    public function validateInputs($validations) {
        foreach($validations as $v) {
            $name = $v[0];
            $input = $this->input($name);
            $rules = $v[1];
            if(!$this->validater->run($input, $rules)){
                return false;
            }
        }
        return true;
    }
}
