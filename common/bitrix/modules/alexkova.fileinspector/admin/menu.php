<?
IncludeModuleLangFile(__FILE__);
$module_id = "alexkova.fileinspector";

$aMenuBase = array();
$aMenuExt = array();

$aMenuExt[] = array(
	"text" => GetMessage('FILEINSPECTOR_SCANNER'),
	"url" => "{$module_id}_scanner.php?lang=".LANGUAGE_ID,
	"dynamic" => false,
	"title" => GetMessage('FILEINSPECTOR_SCANNER_TITLE'),
	"sort" => 100,
	"items_id" => 'fileinspector_scanner',
	"items" => array(),
);

$aMenuExt[] = array(
	"text" => GetMessage('FILEINSPECTOR_LISTPAGE'),
	"url" => "{$module_id}_list.php?lang=".LANGUAGE_ID,
	"dynamic" => false,
	"title" => GetMessage('FILEINSPECTOR_LISTPAGE_TITLE'),
	"sort" => 100,
	"items_id" => 'fileinspector_scanner',
	"items" => array(),
);

$aMenuExt[] = array(
	"text" => GetMessage('FILEINSPECTOR_STATPAGE'),
	"url" => "{$module_id}_stat.php?lang=".LANGUAGE_ID,
	"dynamic" => false,
	"title" => GetMessage('FILEINSPECTOR_STATPAGE_TITLE'),
	"sort" => 100,
	"items_id" => 'fileinspector_stat',
	"items" => array(),
);

$aMenuBase[] = array(
	"parent_menu" => "global_menu_settings",
	"section" => 'fileinspector',
	"text" => GetMessage('FILEINSPECTOR_MENU'),
	"url" => "{$module_id}_scanner.php?lang=".LANGUAGE_ID,
	"dynamic" => false,
	"items_id" => 'fileinspector',
	"title" => GetMessage('FILEINSPECTOR_MENU_TITLE'),
	"sort" => 3000,
	"items" => $aMenuExt,
);

$aMenuExt = array();

$aMenuExt[] = array(
	"text" => GetMessage('FILEINSPECTOR_BFILE_SCANNER'),
	"url" => "{$module_id}_bfuser_scanner.php?lang=".LANGUAGE_ID,
	"dynamic" => false,
	"title" => GetMessage('FILEINSPECTOR_BFILE_SCANNER_TITLE'),
	"sort" => 100,
	"items_id" => 'fileinspector_bfuser_scanner',
	"items" => array(),
);

$aMenuBase[] = array(
	"parent_menu" => "global_menu_settings",
	"section" => 'fileinspector',
	"text" => GetMessage('FILEINSPECTOR_MENUOTHER'),
	"url" => "{$module_id}_bfuser_scanner.php?lang=".LANGUAGE_ID,
	"dynamic" => false,
	"items_id" => 'fileinspectorother',
	"title" => GetMessage('FILEINSPECTOR_MENUOTHER_TITLE'),
	"sort" => 3001,
	"items" => $aMenuExt,
);

return $aMenuBase;

?>

