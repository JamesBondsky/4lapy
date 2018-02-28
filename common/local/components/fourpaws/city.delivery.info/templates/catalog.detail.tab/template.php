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
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;
use FourPaws\DeliveryBundle\Entity\IntervalRule\TimeRuleInterface;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\App\Templates\ViewsEnum;

$currentHasInnerDelivery = $arResult['CURRENT']['DELIVERY']['CODE'] == DeliveryService::INNER_DELIVERY_CODE;
$currentDelivery = $arResult['CURRENT']['DELIVERY'];
$defaultHasInnerDelivery = $arResult['DEFAULT']['DELIVERY']['CODE'] == DeliveryService::INNER_DELIVERY_CODE;
$defaultDelivery = $arResult['DEFAULT']['DELIVERY'];
?>

<?php $this->SetViewTarget(ViewsEnum::PRODUCT_DETAIL_DELIVERY_PAYMENT_TAB_HEADER) ?>
<li class="b-tab-title__item js-tab-item" <?= $currentHasInnerDelivery ? '' : 'style="display:none"' ?>>
    <a class="b-tab-title__link js-tab-link"
       href="javascript:void(0);" title="Доставка и оплата"
       data-tab="data"><span class="b-tab-title__text">Доставка и оплата</span></a>
</li>
<?php $this->EndViewTarget() ?>

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
        <?php
        $intervalData = [];
        /** @var IntervalCollection $intervals */
        $intervals = $currentDelivery['INTERVALS'];
        /** @var Interval $interval */
        foreach ($intervals as $interval) {
            /** @var BaseRule $rule */
            foreach ($interval->getRules() as $rule) {
                if (!$rule instanceof TimeRuleInterface) {
                    continue;
                }

                $to = $rule->getTo();
                if ($to === 0) {
                    $to = 24;
                }
                if (!isset($intervalData[$to]) || $intervalData[$to] > $rule->getValue()) {
                    $intervalData[$to] = $rule->getValue();
                }
            }
        }
        ksort($intervalData);
        $intervalData = array_flip($intervalData);
        ?>
        <?php if (!empty($intervalData)) { ?>
            <div class="b-tab-shipping__inline-table b-tab-shipping__inline-table--right">
                <table class="b-tab-shipping__table">
                    <caption class="b-tab-shipping__caption">Время доставки</caption>
                    <tbody class="b-tab-shipping__tbody">
                    <tr class="b-tab-shipping__tr">
                        <th class="b-tab-shipping__th b-tab-shipping__th--first">Время заказа</th>
                        <th class="b-tab-shipping__th b-tab-shipping__th--second">Доставка</th>
                    </tr>
                    <?php
                    $intervalCount = count($intervalData);
                    end($intervalData);
                    $lastKey = key($intervalData);
                    $lastHour = null;
                    ?>
                    <?php foreach ($intervalData as $days => $to) { ?>
                        <tr class="b-tab-shipping__tr b-tab-shipping__tr--first-line">
                            <td class="b-tab-shipping__td b-tab-shipping__td--first">
                                <?php if (($intervalCount > 1) && ($days === $lastKey)) { ?>
                                    после <?= $lastHour === 24 ? 0 : $lastHour ?>:00
                                <?php } else { ?>
                                    до <?= $to === 24 ? 0 : $to ?>:00
                                <?php } ?>
                            </td>
                            <td class="b-tab-shipping__td b-tab-shipping__td--second">
                                <?php if ($days === 0) { ?>
                                    в тот же день
                                <?php } elseif ($days === 1) { ?>
                                    на следующий день
                                <?php } else { ?>
                                    через <?= $days ?> <?= (new Declension('день', 'дня', 'дней'))->get($days) ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php $lastHour = $to ?>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</div>
