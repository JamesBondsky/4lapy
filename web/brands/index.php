<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Бренды");
?>
<?$APPLICATION->IncludeComponent(
	'fourpaws:brands',
	'fp.17.0',
	array(
		'CACHE_TYPE' => 'A',
		'CACHE_TIME' => '43200',
		'SEF_MODE' => 'Y',
		'SEF_FOLDER' => '/brands/',
		'SEF_URL_TEMPLATES' => array(
			'index' => 'index.php',
			//'letter' => '#LETTER_REDUCED#/'
		),
	),
	null,
	array(
		'HIDE_ICONS' => 'Y'
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>