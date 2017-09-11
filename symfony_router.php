<?php

use FourPaws\App\App;
use Symfony\Component\HttpFoundation\Request;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include.php';

App::handleRequest(Request::createFromGlobals());
