<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array $arParams
 * @var array $arResult
 * @var array $templateData
 *
 * @var string $componentPath
 * @var string $templateName
 * @var string $templateFile
 * @var string $templateFolder
 * @var CBitrixComponentTemplate $this
 *
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\App\Templates\ViewsEnum;
$this->setFrameMode(true);

$currentHasInnerDelivery = $arResult['CURRENT']['DELIVERY']['CODE'] == DeliveryService::INNER_DELIVERY_CODE;
$currentDelivery = $arResult['CURRENT']['DELIVERY'];
$defaultHasInnerDelivery = $arResult['DEFAULT']['DELIVERY']['CODE'] == DeliveryService::INNER_DELIVERY_CODE;
$defaultDelivery = $arResult['DEFAULT']['DELIVERY'];
?>

<?php $this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_DELIVERY_PAYMENT_TAB_HEADER) ?>
<?php $frame = $this->createFrame()->begin() ?>
<li class="b-tab-title__item js-tab-item" <?= $currentHasInnerDelivery ? '' : 'style="display:none"' ?>>
    <a class="b-tab-title__link js-tab-link"
       href="javascript:void(0);" title="Доставка и оплата"
       data-tab="data"><span class="b-tab-title__text">Доставка и оплата</span></a>
</li>
<?php $frame->beginStub() ?>
<li class="b-tab-title__item js-tab-item" <?= $defaultHasInnerDelivery ? '' : 'style="display:none"' ?>>
    <a class="b-tab-title__link js-tab-link"
       href="javascript:void(0);" title="Доставка и оплата"
       data-tab="data"><span class="b-tab-title__text">Доставка и оплата</span></a>
</li>
<?php $frame->end() ?>
<?php $this->EndViewTarget() ?>

<?php $frame = $this->createFrame()->begin() ?>
<div class="b-tab-content__container js-tab-content"
     data-tab-content="data" <?= $currentHasInnerDelivery ? '' : 'style="display:none"' ?>>
    <div class="b-tab-shipping">
        <div class="b-tab-shipping__inline-table">
            <table class="b-tab-shipping__table">
                <caption class="b-tab-shipping__caption">Стоимость доставки</caption>
                <tbody class="b-tab-shipping__tbody">
                <tr class="b-tab-shipping__tr">
                    <th class="b-tab-shipping__th b-tab-shipping__th--first">Заказ на сумму</th>
                    <th class="b-tab-shipping__th b-tab-shipping__th--second">Доставка</th>
                </tr>
                <? if ($currentDelivery['FREE_FROM']) { ?>
                    <tr class="b-tab-shipping__tr">
                        <td class="b-tab-shipping__td b-tab-shipping__td--first">0
                            — <?= $currentDelivery['FREE_FROM'] - 1 ?>
                            <span class="b-ruble b-ruble--table-tab-shipping"> ₽</span>
                        </td>
                        <td class="b-tab-shipping__td b-tab-shipping__td--second"><?= $currentDelivery['PRICE'] ?>
                            <span class="b-ruble b-ruble--table-tab-shipping"> ₽</span>
                        </td>
                    </tr>
                    <tr class="b-tab-shipping__tr">
                        <td class="b-tab-shipping__td b-tab-shipping__td--first">от <?= $currentDelivery['FREE_FROM'] ?>
                        </td>
                        <td class="b-tab-shipping__td b-tab-shipping__td--second">бесплатно</td>
                    </tr>
                <? } else { ?>
                    <tr class="b-tab-shipping__tr">
                        <td class="b-tab-shipping__td b-tab-shipping__td--first">на любую сумму</td>
                        <td class="b-tab-shipping__td b-tab-shipping__td--second"><?= $currentDelivery['PRICE'] ?></td>
                    </tr>
                <? } ?>
                </tbody>
            </table>
        </div>
        <div class="b-tab-shipping__inline-table b-tab-shipping__inline-table--right">
            <table class="b-tab-shipping__table">
                <caption class="b-tab-shipping__caption">Время доставки</caption>
                <tbody class="b-tab-shipping__tbody">
                <tr class="b-tab-shipping__tr">
                    <th class="b-tab-shipping__th b-tab-shipping__th--first">Время заказа</th>
                    <th class="b-tab-shipping__th b-tab-shipping__th--second">Доставка</th>
                </tr>
                <tr class="b-tab-shipping__tr b-tab-shipping__tr--first-line">
                    <td class="b-tab-shipping__td b-tab-shipping__td--first">до 14:00</td>
                    <td class="b-tab-shipping__td b-tab-shipping__td--second">в тот же день</td>
                </tr>
                <tr class="b-tab-shipping__tr">
                    <td class="b-tab-shipping__td b-tab-shipping__td--first">до 20:00</td>
                    <td class="b-tab-shipping__td b-tab-shipping__td--second">на следующий день</td>
                </tr>
                <tr class="b-tab-shipping__tr">
                    <td class="b-tab-shipping__td b-tab-shipping__td--first">после 20:00</td>
                    <td class="b-tab-shipping__td b-tab-shipping__td--second">по договоренности</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $frame->beginStub() ?>
<div class="b-tab-content__container js-tab-content"
     data-tab-content="data" <?= $defaultHasInnerDelivery ? '' : 'style="display:none"' ?>>
    <div class="b-tab-shipping">
        <div class="b-tab-shipping__inline-table">
            <table class="b-tab-shipping__table">
                <caption class="b-tab-shipping__caption">Стоимость доставки</caption>
                <tbody class="b-tab-shipping__tbody">
                <tr class="b-tab-shipping__tr">
                    <th class="b-tab-shipping__th b-tab-shipping__th--first">Заказ на сумму</th>
                    <th class="b-tab-shipping__th b-tab-shipping__th--second">Доставка</th>
                </tr>
                <? if ($defaultDelivery['FREE_FROM']) { ?>
                    <tr class="b-tab-shipping__tr">
                        <td class="b-tab-shipping__td b-tab-shipping__td--first">0
                            — <?= $defaultDelivery['FREE_FROM'] - 1 ?>
                            <span class="b-ruble b-ruble--table-tab-shipping"> ₽</span>
                        </td>
                        <td class="b-tab-shipping__td b-tab-shipping__td--second"><?= $defaultDelivery['PRICE'] ?>
                            <span class="b-ruble b-ruble--table-tab-shipping"> ₽</span>
                        </td>
                    </tr>
                    <tr class="b-tab-shipping__tr">
                        <td class="b-tab-shipping__td b-tab-shipping__td--first">от <?= $defaultDelivery['FREE_FROM'] ?>
                        </td>
                        <td class="b-tab-shipping__td b-tab-shipping__td--second">бесплатно</td>
                    </tr>
                <? } else { ?>
                    <tr class="b-tab-shipping__tr">
                        <td class="b-tab-shipping__td b-tab-shipping__td--first">на любую сумму</td>
                        <td class="b-tab-shipping__td b-tab-shipping__td--second"><?= $defaultDelivery['PRICE'] ?></td>
                    </tr>
                <? } ?>
                </tbody>
            </table>
        </div>
        <div class="b-tab-shipping__inline-table b-tab-shipping__inline-table--right">
            <table class="b-tab-shipping__table">
                <caption class="b-tab-shipping__caption">Время доставки</caption>
                <tbody class="b-tab-shipping__tbody">
                <tr class="b-tab-shipping__tr">
                    <th class="b-tab-shipping__th b-tab-shipping__th--first">Время заказа</th>
                    <th class="b-tab-shipping__th b-tab-shipping__th--second">Доставка</th>
                </tr>
                <tr class="b-tab-shipping__tr b-tab-shipping__tr--first-line">
                    <td class="b-tab-shipping__td b-tab-shipping__td--first">до 14:00</td>
                    <td class="b-tab-shipping__td b-tab-shipping__td--second">в тот же день</td>
                </tr>
                <tr class="b-tab-shipping__tr">
                    <td class="b-tab-shipping__td b-tab-shipping__td--first">до 20:00</td>
                    <td class="b-tab-shipping__td b-tab-shipping__td--second">на следующий день</td>
                </tr>
                <tr class="b-tab-shipping__tr">
                    <td class="b-tab-shipping__td b-tab-shipping__td--first">после 20:00</td>
                    <td class="b-tab-shipping__td b-tab-shipping__td--second">по договоренности</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $frame->end() ?>
