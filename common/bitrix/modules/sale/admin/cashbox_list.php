<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('sale');

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page;
use Bitrix\Sale\Cashbox;

$publicMode = $adminPage->publicMode;
$selfFolderUrl = $adminPage->getSelfFolderUrl();

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
Page\Asset::getInstance()->addJs("/bitrix/js/sale/cashbox.js");
CJSCore::Init(array('clipboard'));

$tableId = "tbl_sale_cashbox";
$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$lang = $context->getLanguage();
$request = $context->getRequest();

$oSort = new CAdminSorting($tableId, "ID", "asc");
$lAdmin = new CAdminUiList($tableId, $oSort);

$filterFields = array(
	array(
		"id" => "NAME",
		"name" => GetMessage("SALE_CASHBOX_NAME"),
		"filterable" => "%",
		"quickSearch" => "%",
		"default" => true
	),
	array(
		"id" => "ACTIVE",
		"name" => GetMessage("SALE_F_ACTIVE"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("SALE_YES"),
			"N" => GetMessage("SALE_NO")
		),
		"filterable" => "",
		"default" => true
	)
);

$filter = array();

$lAdmin->AddFilter($filterFields, $filter);

if (($ids = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($request->get('action_target')=='selected')
	{
		$ids = array();
		$dbRes = \Bitrix\Sale\Cashbox\Internals\CashboxTable::getList(
			array(
				'select' => array('ID'),
				'filter' => $filter,
				'order' => array(ToUpper($by) => ToUpper($order))
			)
		);

		while ($arResult = $dbRes->fetch())
			$ids[] = $arResult['ID'];
	}

	foreach ($ids as $id)
	{
		if ((int)$id <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				if ($id == \Bitrix\Sale\Cashbox\Cashbox1C::getId())
				{
					$lAdmin->AddGroupError(GetMessage("SPSAN_ERROR_DELETE_1C"), $id);
					continue;
				}

				$result = Cashbox\Manager::delete($id);
				if (!$result->isSuccess())
				{
					if ($result->getErrorMessages())
						$lAdmin->AddGroupError(join(', ', $result->getErrorMessages()), $id);
					else
						$lAdmin->AddGroupError(GetMessage("SPSAN_ERROR_DELETE"), $id);
				}

				break;

			case "activate":
			case "deactivate":

				$arFields = array(
					"ACTIVE" => ($_REQUEST['action'] == 'activate') ? 'Y' : 'N'
				);

				$result = Cashbox\Manager::update($id, $arFields);
				if (!$result->isSuccess())
				{
					if ($result->getErrorMessages())
						$lAdmin->AddGroupError(join(', ', $result->getErrorMessages()), $id);
					else
						$lAdmin->AddGroupError(GetMessage("SPSAN_ERROR_UPDATE"), $id);
				}

				break;
		}
	}
	if ($lAdmin->hasGroupErrors())
	{
		$adminSidePanelHelper->sendJsonErrorResponse($lAdmin->getGroupErrors());
	}
	else
	{
		$adminSidePanelHelper->sendSuccessResponse();
	}
}

if ($publicMode)
{
	$filter['!ID'] = Cashbox\Cashbox1C::getId();
}

$params = array(
	'select' => array('*'),
	'filter' => $filter
);

global $by, $order;
$by = isset($by) ? $by : "ID";
$order = isset($order) ? $order : "ASC";
$params['order'] = array($by => $order);

$dbResultList = new CAdminUiResult(\Bitrix\Sale\Cashbox\Internals\CashboxTable::getList($params), $tableId);
$dbResultList->NavStart();

$headers = array(
	array("id" => "ID", "content" => GetMessage("SALE_CASHBOX_ID"), "sort" => "ID", "default" => true),
	array("id" => "NAME", "content" => GetMessage("SALE_CASHBOX_NAME"), "sort" => "NAME", "default" => true),
	array("id" => "ACTIVE", "content" => GetMessage("SALE_CASHBOX_ACTIVE"), "sort" => "ACTIVE", "default" => true),
	array("id" => "SORT", "content" => GetMessage("SALE_CASHBOX_SORT"), "sort" => "SORT", "default" => true),
	array("id" => "DATE_CREATE", "content" => GetMessage("SALE_CASHBOX_DATE_CREATE"), "sort" => "DATE_CREATE", "default" => true),
	array("id" => "NUMBER_KKM", "content" => GetMessage("SALE_CASHBOX_NUMBER_KKM"), "sort" => "KKM_ID", "default" => true),
	array("id" => "ENABLED", "content" => GetMessage("SALE_CASHBOX_LAST_CHECK_STATUS"), "sort" => "ENABLED", "default" => true),
	array("id" => "DATE_LAST_CHECK", "content" => GetMessage("SALE_CASHBOX_DATE_LAST_CHECK"), "default" => true),
);


$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."sale_cashbox_list.php"));

$lAdmin->AddHeaders($headers);

$visibleHeaders = $lAdmin->GetVisibleHeaderColumns();

while ($cashbox = $dbResultList->Fetch())
{
	$editUrl = $selfFolderUrl."sale_cashbox_edit.php?ID=".$cashbox['ID']."&lang=".LANGUAGE_ID;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$row =& $lAdmin->AddRow($cashbox['ID'], $cashbox, $editUrl, GetMessage("SALE_EDIT_DESCR"));

	$row->AddField("ID", "<a href=\"".$editUrl."\">".$cashbox['ID']."</a>");
	$row->AddField("NAME", htmlspecialcharsbx($cashbox['NAME']));
	$row->AddField("ACTIVE", (($cashbox['ACTIVE']=="Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO")));
	$row->AddField("SORT", $cashbox['SORT']);
	$row->AddField("DATE_CREATE", $cashbox['DATE_CREATE']);
	$row->AddField("NUMBER_KKM", htmlspecialcharsbx($cashbox['NUMBER_KKM']));

	$enabled = $cashbox['ENABLED'] === 'Y' ? 'Y' : 'N';
	$row->AddField("ENABLED", GetMessage("SALE_CASHBOX_LAST_CHECK_STATUS_".$enabled));
	$row->AddField("DATE_LAST_CHECK", $cashbox['DATE_LAST_CHECK']);

	$arActions = array(
		array(
			"ICON" => "edit",
			"TEXT" => GetMessage("SALE_CASHBOX_EDIT"),
			"TITLE" => GetMessage("SALE_CASHBOX_EDIT_DESCR"),
			"LINK" => $editUrl,
			"DEFAULT" => true,
		),
	);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("SALE_CASHBOX_DELETE"),
			"TITLE" => GetMessage("SALE_CASHBOX_DELETE_DESCR"),
			"ACTION" => "if(confirm('".GetMessage('SALE_CASHBOX_DELETE_CONFIRM', array('#CASHBOX_ID#' => $cashbox['ID']))."')) ".$lAdmin->ActionDoGroup($cashbox['ID'], "delete"),
		);
	}

	$row->AddActions($arActions);
}

if ($saleModulePermissions == "W")
{
	$lAdmin->AddGroupActionTable(
		array(
			"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
			"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
			"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		)
	);
	$addUrl = $selfFolderUrl."sale_cashbox_edit.php?lang=".$lang;
	$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
	$aContext = array(
		array(
			"TEXT" => GetMessage("SALE_CASHBOX_ADD_NEW"),
			"LINK" => $addUrl,
			"ICON" => "btn_new",
		)
	);
	if (!$publicMode)
	{
		$aContext[] = array(
			"TEXT" => GetMessage("SALE_CASHBOX_GENERATE_LINK"),
			"ICON" => "btn_new",
			'ONCLICK' => 'BX.Sale.Cashbox.generateConnectionLink()'
		);
		/** @global CUser $USER */
		global $USER;
		if($USER->CanDoOperation("install_updates"))
		{
			$aContext[] = array(
				"TEXT" => GetMessage("SALE_MARKETPLACE_ADD_NEW"),
				"TITLE" => GetMessage("SALE_MARKETPLACE_ADD_NEW_ALT"),
				"LINK" => "update_system_market.php?category=149&lang=".LANGUAGE_ID,
				"ICON" => "btn"
			);
		}
	}

	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_cashbox_list.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SALE_CASHBOX_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<script language="JavaScript">
	BX.message(
		{
			SALE_CASHBOX_COPY: '<?=Loc::getMessage("SALE_CASHBOX_COPY")?>',
			SALE_CASHBOX_WINDOW_TITLE: '<?=Loc::getMessage("SALE_CASHBOX_WINDOW_TITLE")?>',
			SALE_CASHBOX_WINDOW_STEP_1: '<?=Loc::getMessage("SALE_CASHBOX_WINDOW_STEP_1")?>',
			SALE_CASHBOX_WINDOW_STEP_2: '<?=Loc::getMessage("SALE_CASHBOX_WINDOW_STEP_2")?>',
		}
	);
</script>
<?

if (!Cashbox\Manager::isSupportedFFD105())
{
	Cashbox\Cashbox::init();

	$cashboxList = Cashbox\Manager::getListFromCache();
	$cashboxFfd105 = array();
	$cashboxNoFfd105 = array();
	foreach ($cashboxList as $cashbox)
	{
		if ($cashbox['ACTIVE'] === 'N')
			continue;

		/** @var Cashbox\Cashbox $handler */
		$handler = $cashbox['HANDLER'];
		if ($handler::isSupportedFFD105())
		{
			$cashboxFfd105[] = htmlspecialcharsbx($cashbox['NAME']);
		}
		else
		{
			$cashboxNoFfd105[] = htmlspecialcharsbx($cashbox['NAME']);
		}
	}

	if ($cashboxFfd105)
	{
		$note = BeginNote();
		$note .= Loc::getMessage(
			'SALE_CASHBOX_VERSION_CONFLICT',
			array(
				'#CASHBOX_FFD105#' => implode(', ', $cashboxFfd105),
				'#CASHBOX_NO_FFD105#' => implode(', ', $cashboxNoFfd105)
			)
		);
		$note .= EndNote();
		echo $note;
	}
}
$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");

?>