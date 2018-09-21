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

use Bitrix\Main\Grid\Declension;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\DeliveryBundle\Dto\IntervalRuleResult;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\App\Templates\ViewsEnum;

$hasInnerDelivery = $arResult['DELIVERY']['CODE'] === DeliveryService::INNER_DELIVERY_CODE;
$delivery = $arResult['DELIVERY'];
?>

<?php $this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_DELIVERY_PAYMENT_TAB_HEADER) ?>
<li class="b-tab-title__item js-tab-item" <?= $hasInnerDelivery ? '' : 'style="display:none"' ?>>
    <a class="b-tab-title__link js-tab-link"
       href="javascript:void(0);" title="Доставка и оплата"
       data-tab="data"><span class="b-tab-title__text">Доставка и оплата</span></a>
</li>
<?php $this->EndViewTarget() ?>

<div class="b-tab-content__container js-tab-content"
     data-tab-content="data" <?= $hasInnerDelivery ? '' : 'style="display:none"' ?>>
    <div class="b-tab-shipping">
        <div class="b-tab-shipping__inline-table">
            <table class="b-tab-shipping__table">
                <caption class="b-tab-shipping__caption">Стоимость доставки</caption>
                <tbody class="b-tab-shipping__tbody">
                <tr class="b-tab-shipping__tr">
                    <th class="b-tab-shipping__th b-tab-shipping__th--first">Заказ на сумму</th>
                    <th class="b-tab-shipping__th b-tab-shipping__th--second">Доставка</th>
                </tr>
                <? if ($delivery['FREE_FROM']) { ?>
                    <tr class="b-tab-shipping__tr">
                        <td class="b-tab-shipping__td b-tab-shipping__td--first">0
                            — <?= $delivery['FREE_FROM'] - 1 ?>
                            <span class="b-ruble b-ruble--table-tab-shipping"> ₽</span>
                        </td>
                        <td class="b-tab-shipping__td b-tab-shipping__td--second"><?= $delivery['PRICE'] ?>
                            <span class="b-ruble b-ruble--table-tab-shipping"> ₽</span>
                        </td>
                    </tr>
                    <tr class="b-tab-shipping__tr">
                        <td class="b-tab-shipping__td b-tab-shipping__td--first">от <?= $delivery['FREE_FROM'] ?>
                        </td>
                        <td class="b-tab-shipping__td b-tab-shipping__td--second">бесплатно</td>
                    </tr>
                <? } else { ?>
                    <tr class="b-tab-shipping__tr">
                        <td class="b-tab-shipping__td b-tab-shipping__td--first">на любую сумму</td>
                        <td class="b-tab-shipping__td b-tab-shipping__td--second"><?= $delivery['PRICE'] ?></td>
                    </tr>
                <? } ?>
                </tbody>
            </table>
        </div>
        <?php
        /** @var ArrayCollection $intervalDays */
        $intervalDays = $delivery['INTERVAL_DAYS'];
        if (!$intervalDays->isEmpty()) { ?>
            <div class="b-tab-shipping__inline-table b-tab-shipping__inline-table--right">
                <table class="b-tab-shipping__table">
                    <caption class="b-tab-shipping__caption">Время доставки</caption>
                    <tbody class="b-tab-shipping__tbody">
                    <tr class="b-tab-shipping__tr">
                        <th class="b-tab-shipping__th b-tab-shipping__th--first">Время заказа</th>
                        <th class="b-tab-shipping__th b-tab-shipping__th--second">Доставка</th>
                    </tr>
                    <?php
                    /** @var IntervalRuleResult $intervalResult */
                    foreach ($intervalDays as $intervalResult) { ?>
                        <tr class="b-tab-shipping__tr b-tab-shipping__tr--first-line">
                            <td class="b-tab-shipping__td b-tab-shipping__td--first">
                                <?php if ($intervalResult->getTimeTo() === 0) { ?>
                                    после <?= $intervalResult->getTimeFrom() ?>:00
                                <?php } else { ?>
                                    до <?= $intervalResult->getTimeTo() ?>:00
                                <?php } ?>
                            </td>
                            <td class="b-tab-shipping__td b-tab-shipping__td--second">
                                <?php if ($intervalResult->getDays() === 0) { ?>
                                    в тот же день
                                <?php } elseif ($intervalResult->getDays() === 1) { ?>
                                    на следующий день
                                <?php } else { ?>
                                    через <?= $intervalResult->getDays() ?> <?= (new Declension('день', 'дня', 'дней'))->get($intervalResult->getDays()) ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>
