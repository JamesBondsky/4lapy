<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Зарегистрировать карту");
?>
<?$APPLICATION->IncludeComponent(
    'fourpaws:front_office.card.registration',
    'fo.17.0',
    [],
    null,
    [
        'HIDE_ICONS' => 'Y'
    ]
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>