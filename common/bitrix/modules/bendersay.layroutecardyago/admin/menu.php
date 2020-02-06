<?

IncludeModuleLangFile(__FILE__); // � menu.php ����� ��� �� ����� ������������ �������� �����

if ($APPLICATION->GetGroupRight("bendersay.exportimport") >= "R") { // �������� ������ ������� � ������

	$aMenu = [
		"parent_menu" => "global_menu_content",
		"sort" => 100,
		"text" => GetMessage("BENDERSAY_EXPORTIMPORT_MENU_TITLE"),
		"title"=> GetMessage("BENDERSAY_EXPORTIMPORT_MENU_TITLE"),
		"icon" => "highloadblock_menu_icon",
		"page_icon" => "highloadblock_page_icon",
		"items_id" => "menu_ben",
		"items" => [
			[
				"sort" => 1,
				"text"=> GetMessage("BENDERSAY_EXPORTIMPORT_MENU_EXPORT"),
				"title"=> GetMessage("BENDERSAY_EXPORTIMPORT_MENU_EXPORT"),
				"items_id" => "menu_ben_export",
				"items" => [
					[
						"sort" => 1,
						"text"=> GetMessage("BENDERSAY_EXPORTIMPORT_MENU_EXPORT_CSV"),
						"title"=> GetMessage("BENDERSAY_EXPORTIMPORT_MENU_EXPORT_CSV"),
						"url"=> "/bitrix/admin/bendersay_exportimport_ExportCSV.php",
					],
					[
						"sort" => 2,
						"text"=> GetMessage("BENDERSAY_EXPORTIMPORT_MENU_EXPORT_JSON"),
						"title"=> GetMessage("BENDERSAY_EXPORTIMPORT_MENU_EXPORT_JSON"),
						"url"=>"/bitrix/admin/bendersay_exportimport_ExportJSON.php",
					],
				]
			],
			[
				"sort" => 2,
				"text"=> GetMessage("BENDERSAY_EXPORTIMPORT_MENU_IMPORT"),
				"title"=> GetMessage("BENDERSAY_EXPORTIMPORT_MENU_IMPORT"),
				"items_id" => "menu_ben_import",
				"items" => [
					[
						"sort" => 1,
						"text"=> GetMessage("BENDERSAY_EXPORTIMPORT_MENU_IMPORT_CSV"),
						"title"=> GetMessage("BENDERSAY_EXPORTIMPORT_MENU_IMPORT_CSV"),
						"url"=>"/bitrix/admin/bendersay_exportimport_ImportCSV.php",
					],
					[
						"sort" => 2,
						"text"=> GetMessage("BENDERSAY_EXPORTIMPORT_MENU_IMPORT_JSON"),
						"title"=> GetMessage("BENDERSAY_EXPORTIMPORT_MENU_IMPORT_JSON"),
						"url"=>"/bitrix/admin/bendersay_exportimport_ImportJSON.php",
					],
				]
			]
		]
	];

	// ������ ���������� ������
	return $aMenu;
}
// ���� ��� �������, ������ false
return false;
