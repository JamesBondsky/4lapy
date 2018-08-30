<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Зарегистрировать покупателя");
?>
<?
$APPLICATION->IncludeComponent(
    'fourpaws:front_office.customer.registration',
    'fo.17.0',
    [
        'SEND_USER_REGISTRATION_SMS' => 'N',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);
?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>