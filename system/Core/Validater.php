<?php
namespace Spaf\Core;

class Validater {

    public function run($value, $rules)
    {
        if(!is_array($rules)) {
            $rules = explode('|', $rules);
        }
        foreach ($rules as $rule) {
            $params= [];
            if(strpos($rule, '[') !== false) {
                $x = explode('[', $rule);
                $rule = current($x);
                $params = explode(',', substr($x[1],0,-1));
            }

            // is no existe y no es requerido, lo valida
            if( ($rule === 'if_exists') && is_null($value) ) {
                return true;
            }

            if(method_exists($this, $rule)) {
                if(!$this->$rule($value, $params)) {
                    return false;
                }
            }
        }
        return true;
    }

    // Validaciones

    public function is_natural_no_zero($str, $params)
    {
        if( ! preg_match( '/^[0-9]*$/', $str)) {
            return false;
        }

        if($str == 0){
            return false;
        }

        return true;
    }

    public function length($str, $params)
    {
        if(!count($params)) {
            return true;
        }
        $min = (int) $params[0];
        $max = $params[1] ?? $min;
        return strlen($str) >= $min && strlen($str) <= $max;
    }
}
