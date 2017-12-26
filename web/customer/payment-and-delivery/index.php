<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Доставка и оплата");
?>
<div class="b-container b-container--delivery">
    <?php $APPLICATION->IncludeComponent(
        'fourpaws:city.selector',
        'delivery.page',
        [],
        false,
        ['HIDE_ICONS' => 'Y']
    );
    ?>
</div>
<div class="b-container b-container--delivery b-container--delivery__date">
    <?php $APPLICATION->IncludeComponent(
        'fourpaws:city.delivery.info',
        'delivery.page',
        [],
        false,
        ['HIDE_ICONS' => 'Y']
    );
    ?>
</div>
<div class="b-container b-container--delivery b-container--delivery__date">
    <?php $APPLICATION->IncludeComponent(
        'bitrix:main.include',
        '',
        [
            'COMPONENT_TEMPLATE' => '.default',
            'AREA_FILE_SHOW'     => 'file',
            'PATH'               => '/local/include/blocks/delivery_page.payments.php',
            'EDIT_TEMPLATE'      => '',
        ],
        false
    ); ?>
</div>

<?php
$APPLICATION->IncludeComponent(
    'fourpaws:city.phone',
    'delivery.page',
    [],
    false,
    ['HIDE_ICONS' => 'Y']
);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
