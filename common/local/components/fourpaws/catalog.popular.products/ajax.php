<?
define('STOP_STATISTICS', true);
define('DisableEventsCheck', true);
define('NO_AGENT_CHECK', true);

$siteId = isset($_REQUEST['siteId']) && is_string($_REQUEST['siteId']) ? $_REQUEST['siteId'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId)) {
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
/** @global \CMain $APPLICATION */

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter());

if (!\Bitrix\Main\Loader::includeModule('iblock')) {
    return;
}

$signer = new \Bitrix\Main\Security\Sign\Signer();
try {
	$template = $signer->unsign($request->get('template'), 'catalog.popular.products');
	$paramString = $signer->unsign($request->get('parameters'), 'catalog.popular.products');
} catch (\Bitrix\Main\Security\Sign\BadSignatureException $e) {
	die();
}

$parameters = unserialize(base64_decode($paramString));
$parent = null;
if (isset($parameters['PARENT_NAME'])) {
	$parent = new CBitrixComponent();
	$parent->InitComponent($parameters['PARENT_NAME'], $parameters['PARENT_TEMPLATE_NAME']);
	$parent->InitComponentTemplate($parameters['PARENT_TEMPLATE_PAGE']);
}

$APPLICATION->RestartBuffer();
$APPLICATION->IncludeComponent(
	'fourpaws:catalog.popular.products',
	$template,
	$parameters,
	$parent
);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
