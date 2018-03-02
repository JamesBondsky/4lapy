<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Быстрый заказ'); ?>
    <section class="b-popup-one-click" style="display: block; border:none;">
        <?php $APPLICATION->IncludeComponent(
            'fourpaws:fast.order',
            '',
            [
                'TYPE'      => 'success',
                'LOAD_TYPE' => 'default',
            ],
            null,
            ['HIDE_ICONS' => 'Y']
        ); ?>
    </section>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';