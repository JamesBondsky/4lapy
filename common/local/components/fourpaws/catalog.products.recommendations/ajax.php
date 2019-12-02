<?php
define('STOP_STATISTICS', true);
define('DisableEventsCheck', true);
define('NO_AGENT_CHECK', true);

$siteId = isset($_REQUEST['siteId']) && is_string($_REQUEST['siteId']) ? $_REQUEST['siteId'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId)) {
    define('SITE_ID', $siteId);
}

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
/** @global \CMain $APPLICATION */

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter());

if (!\Bitrix\Main\Loader::includeModule('iblock')) {
    return;
}

/**
 * ловим некорректные данные
 */
if (!is_string($request->get('template')) || !is_string($request->get('parameters'))) {
    $logger = \Adv\Bitrixtools\Tools\Log\LoggerFactory::create(
        'FourPawsCatalogProductsRecommendations_Ajax'
    );
    $logger->error(
        'The value of a variable must be of type string',
        [
            'VALUES' => [
                'template' => $request->get('template'),
                'parameters' => $request->get('parameters'),
            ],
            'REQUEST' => $_REQUEST,
            'SERVER' => $_SERVER,
        ]
    );
}

$signer = new \Bitrix\Main\Security\Sign\Signer();
try {
    $template = $signer->unsign(trim($request->get('template')), 'catalog.products.recommendations');
    $paramString = $signer->unsign(trim($request->get('parameters')), 'catalog.products.recommendations');
} catch (\Bitrix\Main\Security\Sign\BadSignatureException $e) {
    die();
}

$parameters = unserialize(base64_decode($paramString), ['allowed_classes' => false]);
$parent = null;
if (isset($parameters['PARENT_NAME'])) {
    $parent = new CBitrixComponent();
    $parent->initComponent($parameters['PARENT_NAME'], $parameters['PARENT_TEMPLATE_NAME']);
    $parent->initComponentTemplate($parameters['PARENT_TEMPLATE_PAGE']);
}

$APPLICATION->RestartBuffer();
$APPLICATION->IncludeComponent(
    'fourpaws:catalog.products.recommendations',
    $template,
    $parameters,
    $parent
);

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php';
