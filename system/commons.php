<?php

if (!function_exists('readEnvFile')) {
    /**
     * Lee la configuración desde un archivo de tipo clave=valor
     * No sanitiza entradas, es responsabilidad del usuario configurar
     * correctamente el archivo de entorno.
     */
    function readEnvFile($file)
    {
        if(!is_file($file)) {
            return null;
        }
        
        if (!is_readable($file)){
            throw new InvalidArgumentException("The .env file is not readable: {$file}");
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // excluyo líneas comentadas
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // tiene una asignación.
            if (strpos($line, '=') !== false) {
                [$name, $value] = explode('=', $line, 2);
                $name  = trim($name);
                $value = trim($value);
                if (!getenv($name, true)) {
                    putenv("{$name}={$value}");
                }
            }
        }
    
    }
}

if (! function_exists('env')) {
    /**
     * Extiende a getenv, manejando booleanos y permitiendo un valor por defecto.
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        // Handle any boolean values
        switch (strtolower($value)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'empty':
                return '';
            case 'null':
                return null;
        }

        return $value;
    }
}

if(!function_exists('response')){
    function response(int $code, $body = '')
    {
        return new \Spaf\Core\Response($code, $body);
    }
}

if (! function_exists('log_message')) {
    /**
     * A convenience/compatibility method for logging events through
     * the Log system.
     *
     * Allowed log levels are:
     *  - emergency
     *  - critical
     *  - error
     *  - warning
     *  - info
     *  - debug
     *
     * @return bool
     */
    function log_message(string $level, string $message)
    {
        $levels = [
            'emergency' => 0,
            'critical' => 1,
            'error' => 2,
            'warning' => 3,
            'info' => 4,
            'debug' => 5
        ];
        $logLevel = env('log_level', 4);
        $level = strtolower($level);
        if(!array_key_exists($level, $levels) || $levels[$level]>$logLevel) {
            return false;
        }
        $requestId = env('request_uid', 'x');
        $dateFormat = env('log_date_format', 'Y-m-d H:i:s');
        if(ENVIRONMENT === 'production') {
            $filepath = env('log_path', ROOTDIR . 'writable/logs/') . strtolower($level) . '.' . env('log_ext', 'log');
        } else {
            $filepath = env('log_path', ROOTDIR . 'writable/logs/') . 'general.' . env('log_ext', 'log');
        }

        if (! $fp = @fopen($filepath, 'ab')) {
            return false;
        }

        // Instantiating DateTime with microseconds appended to initial date is needed for proper support of this format
        if (strpos($dateFormat, 'u') !== false) {
            $microtimeFull  = microtime(true);
            $microtimeShort = sprintf('%06d', ($microtimeFull - floor($microtimeFull)) * 1000000);
            $date           = new DateTime(date('Y-m-d H:i:s.' . $microtimeShort, (int) $microtimeFull));
            $date           = $date->format($this->dateFormat);
        } else {
            $date = date($dateFormat);
        }

        $msg = (ENVIRONMENT!=='production' ? strtoupper($level) . ' ' : '' ) . $date . ' [req.'.$requestId.'] -> ' . $message . PHP_EOL;

        flock($fp, LOCK_EX);
        for ($written = 0, $length = strlen($msg); $written < $length; $written += $result){
            if (($result = fwrite($fp, substr($msg, $written))) === false) {
                break;
            }
        }

        flock($fp, LOCK_UN);
        fclose($fp);
        return is_int($result);
    }
}

if (! function_exists('array_keys_exists')) {
    function array_keys_exists(array $keys, array $array): bool
    {
        $diff = array_diff_key(array_flip($keys), $array);
        return count($diff) === 0;
    }
}