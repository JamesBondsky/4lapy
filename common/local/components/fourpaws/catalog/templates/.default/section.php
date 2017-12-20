<?php
/**
 * @var CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$root = reset(explode('/', $arResult['SECTION_CODE_PATH'], 2));
