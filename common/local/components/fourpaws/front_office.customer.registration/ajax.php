<?php
define('STOP_STATISTICS', true);
define('DisableEventsCheck', true);
define('NO_AGENT_CHECK', true);

// инициализация id сайта
$siteId = '';
if (isset($_REQUEST['ajaxContext']['siteId']) && is_string($_REQUEST['ajaxContext']['siteId'])) {
    $siteId = trim($_REQUEST['ajaxContext']['siteId']);
};
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (strlen($siteId) == 2 || strlen($siteId) == 1) {
    define('SITE_ID', $siteId);
}

// инициализация шаблона сайта (чтобы шаблон искался правильно)
$siteTemplateId = '';
if (isset($_REQUEST['ajaxContext']['siteTemplateId']) && is_string($_REQUEST['ajaxContext']['siteTemplateId'])) {
    $siteTemplateId = trim($_REQUEST['ajaxContext']['siteTemplateId']);
}
if ($siteTemplateId !== '' && !preg_match('/[^a-zA-Z0-9_\.]/i', $siteTemplateId)) {
    define('SITE_TEMPLATE_ID', $siteTemplateId);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
/** @global \CMain $APPLICATION */

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter());

$correct = false;
$template = '';
$paramString = '';
$signer = new \Bitrix\Main\Security\Sign\Signer();
try {
    $ajaxContext = $request->get('ajaxContext');
    if ($ajaxContext && is_array($ajaxContext)) {
        $template = $signer->unsign($ajaxContext['template'], 'front_office.customer.registration');
        $paramString = $signer->unsign($ajaxContext['parameters'], 'front_office.customer.registration');
        $correct = true;
    }
} catch (\Bitrix\Main\Security\Sign\BadSignatureException $e) {
    // ничего не делаем
}

if ($correct) {
    $parameters = unserialize(base64_decode($paramString));
    $parent = null;
    if (isset($parameters['PARENT_NAME'])) {
        $parent = new \CBitrixComponent();
        $parent->InitComponent($parameters['PARENT_NAME'], $parameters['PARENT_TEMPLATE_NAME']);
        $parent->InitComponentTemplate($parameters['PARENT_TEMPLATE_PAGE']);
    }
    
    $APPLICATION->RestartBuffer();
    $APPLICATION->IncludeComponent(
        'fourpaws:front_office.customer.registration',
        $template,
        $parameters,
        $parent
    );
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
