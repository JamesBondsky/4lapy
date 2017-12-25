<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Доставка и оплата");

$APPLICATION->IncludeComponent(
    'fourpaws:city.selector',
    'delivery.page',
    [],
    false,
    ['HIDE_ICONS' => 'Y']
);
$APPLICATION->IncludeComponent(
    'fourpaws:city.delivery.info',
    'delivery.page',
    [],
    false,
    ['HIDE_ICONS' => 'Y']
);
$APPLICATION->IncludeComponent(
    'fourpaws:city.phone',
    'delivery.page',
    [],
    false,
    ['HIDE_ICONS' => 'Y']
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
