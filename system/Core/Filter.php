<?php

namespace Spaf\Core;

use Spaf\Core\Request;
use Spaf\Core\Response;

class Filter{

    public function before(Request $request, $arguments = null)
    {
        return true;
    }

    public function after(Request $request, Response &$response, $arguments = NULL)
    {
        return true;
    }
}
