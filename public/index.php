<?php
// Constante base
define('ROOTDIR', realpath(__DIR__ . '/../') . '/');

include_once ROOTDIR . 'vendor/autoload.php';

// Leemos el archivo de configuración
readEnvFile(ROOTDIR . '.env');
define('ENVIRONMENT', env('environment', 'production'));

// Genero un identificador único para el request
putenv('request_uid=' . substr(uniqid(),8));

if(ENVIRONMENT != 'production') {
    error_reporting(-1);
    ini_set('display_errors', 1);
}

// Ejecutamos la app
use Spaf\App;

$app = new Spaf\App('App\\');

foreach (glob(ROOTDIR . "app/Config/*.php") as $filename) {
    require_once $filename;
}

$app->run();
