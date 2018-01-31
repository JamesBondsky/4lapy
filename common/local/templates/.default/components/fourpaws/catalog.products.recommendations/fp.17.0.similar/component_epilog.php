<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 * @var array $templateData
 * @var string $templateFolder
 * @var CatalogSectionComponent $component
 */

if ($arParams['DEFERRED_LOAD'] === 'Y') {
    \CJSCore::Init(['ajax']);
}
