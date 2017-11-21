<?php
/**
 * Created by PhpStorm.
 * User: Vampi
 * Date: 16.11.2017
 * Time: 16:27
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $USER;
$USER->Authorize(1);
if(file_exists(__FILE__)) {
    unlink(__FILE__);
}
LocalRedirect('/');