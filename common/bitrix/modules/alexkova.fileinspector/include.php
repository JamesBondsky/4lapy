<?
global $DBType;
$module_id = 'alexkova.fileinspector';

IncludeModuleLangFile(__FILE__);

CModule::AddAutoloadClasses(
	$module_id,
	array(
		"CAFITools"=> "classes/general/file.php",
		"Alexkova\\Fileinspector\\StatisticTable"=> "lib/statistic.php",
		"CAFISearcher"=> "classes/general/searcher.php",
		"CAFIblockElementSearcher"=> "classes/general/iblock.php",
		"CAFIblockSearcher"=> "classes/general/iblock.php",
		"CAFICheckFile"=> "classes/general/checkfile.php",
		"CAFIDataTable"=> "classes/general/datatable.php",
		"CAFIBFile"=> "classes/general/bfile.php",
		"CAFIUserFields"=> "classes/general/userfields.php",
		"CAFIStat"=> "classes/general/stat.php",
		"CIFSaleUser"=>"classes/sale/saleuser.php",
		)
	);

?>