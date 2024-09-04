<?php

namespace App\Filters;

use Spaf\Core\Filter;
use Spaf\Core\Request;
use Spaf\Core\Response;

class LogReq extends Filter{

    public function before(Request $request, $arguments = null) {
        log_message("info", "[FILTER:LogReq] Request time " . $arguments[0]);

        return true;
    }

}
