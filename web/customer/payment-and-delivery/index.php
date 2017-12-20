<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Доставка и оплата");

$APPLICATION->IncludeComponent('fourpaws:city.selector', 'delivery.page');
$APPLICATION->IncludeComponent('fourpaws:city.delivery.info', 'delivery.page');
$APPLICATION->IncludeComponent('fourpaws:city.phone', 'delivery.page');

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
