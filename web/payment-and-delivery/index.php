<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty('title', 'Доставка товаров для животных из зоомагазина Четыре Лапы – подробные условия доставки и оплаты зоотоваров в Вашем регионе');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetPageProperty('keywords', '');
$APPLICATION->SetTitle("Доставка товаров для животных из зоомагазина Четыре Лапы – подробные условия доставки и оплаты зоотоваров в Вашем регионе");
?>
<div class="b-container b-container--delivery">
    <div class="b_delivery">
        <h1 class="b-title b-title--h1">Доставка и оплата</h1>
        <div class="b-delivery__town">
            <?php $APPLICATION->IncludeComponent(
                'fourpaws:city.delivery.info',
                'delivery.page.region_info',
                [
                    'CACHE_TIME' => 3600,
                ],
                false,
                ['HIDE_ICONS' => 'Y']
            );
            ?>
        </div>
    </div>
</div>
<div class="b-container b-container--delivery b-container--delivery__date">
    <?php $APPLICATION->IncludeComponent(
        'fourpaws:city.delivery.info',
        'delivery.page',
        ['CACHE_TIME' => 3600],
        false,
        ['HIDE_ICONS' => 'Y']
    );
    ?>
</div>
<div class="b-container b-container--delivery b-container--delivery__payment">
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
