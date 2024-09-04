<?php

$time = time();

$app->routes->get('/', "LogReq[{$time}]|Health::check");
