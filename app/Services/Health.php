<?php 

namespace App\Services;

use Spaf\Core\Service;
use Spaf\Core\Response;
use App\Libraries\SmsMasivos\SmsMasivos;

class Health extends Service{
    protected bool $requireDB = true;

    public function check()
    {
        $estado = [
            'version' => env('version', 0),
            'descripcion' => 'Servicio de Salud'
        ];

        return new Response(Response::HTTP_OK, $estado);
    }
}