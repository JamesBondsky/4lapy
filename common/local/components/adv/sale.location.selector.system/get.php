<?php
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
//define("NOT_CHECK_PERMISSIONS", true);

use Bitrix\Main;
use Bitrix\Main\Loader;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

require_once __DIR__ . '/class.php';
//require_once '/local/components/bitrix/sale.location.selector.system/class.php';

Loader::includeModule('sale');

$result = true;
$errors = array();
$data = array();

try
{
	CUtil::JSPostUnescape();

	if($_REQUEST['REQUEST_TYPE'] === 'get-path') {
        $data = CBitrixLocationSelectorSystemComponentPropLocation::processGetPathRequest($_REQUEST);
        //$data = CBitrixLocationSelectorSystemComponent::processGetPathRequest($_REQUEST);
    }
	else // else type === 'search'
    {
        $data = CBitrixLocationSelectorSystemComponentPropLocation::processSearchRequestV2($_REQUEST);
        //$data = CBitrixLocationSelectorSystemComponent::processSearchRequestV2($_REQUEST);
    }
}
catch(Main\SystemException $e)
{
	$result = false;
	$errors[] = $e->getMessage();
}

header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
print(CUtil::PhpToJSObject(array(
	'result' => $result,
	'errors' => $errors,
	'data' => $data
), false, false, true));