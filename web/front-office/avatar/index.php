<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Аватар");
?>
<?$APPLICATION->IncludeComponent(
    'fourpaws:front_office.avatar',
    'fo.17.0',
    [],
    null,
    [
        'HIDE_ICONS' => 'Y'
    ]
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>