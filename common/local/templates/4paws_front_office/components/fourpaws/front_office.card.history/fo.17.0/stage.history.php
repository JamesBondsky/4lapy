<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var FourPawsFrontOfficeCardRegistrationComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

// форма
include __DIR__.'/inc.form.php';

// Баланс карты
if (!empty($arResult['CURRENT_CARD'])) {
    echo '<div class="active-balance">Активный баланс: '.htmlspecialcharsbx($arResult['CURRENT_CARD']['BALANCE']).'&nbsp;баллов</div>';
}

// История покупок
if (!empty($arResult['CHEQUES'])) {
    $isBonusCard = $arResult['CURRENT_CARD'] && $arResult['CURRENT_CARD']['IS_BONUS_CARD'] === 'Y';
    ?>
    <table class="order-list">
		<thead>
			<tr>
				<th>Детали</th>
				<th>Дата покупки</th>
				<th class="pl10">Адрес магазина</th>
			</tr>
		</thead>
		<tbody>
        <?php
            foreach ($arResult['CHEQUES'] as $cheque) {
                ?>
                <tr>
                    <td class="order-list__i">
                        <span class="order-list__dropdown uppercase" data-id="<?=htmlspecialcharsbx($cheque['CHEQUE_ID'])?>">
                            <span>Детали покупки</span>
                        </span>
                    </td>
                    <td class="order-id order-list__dt"><?=$cheque['DATE']->format('d.m.Y H:i:s')?></td>
                    <td class="order-id order-list__address"><?=htmlspecialcharsbx($cheque['BUSINESS_UNIT_NAME'])?></td>
                </tr>

                <tr>
                    <td colspan="3" class="order-detail-td">
                        <div data-id="<?=htmlspecialcharsbx($cheque['CHEQUE_ID'])?>" class="order-detail" style="display: none;">
                            <table>
                                <thead>
                                    <tr>
                                        <th class="product-name">Наименование</th>
                                        <th>Кол-во</th>
                                        <th>Начислено бонусов</th>
                                    </tr>
                                </thead>
                                <tbody><!-- cheque details (ajax result) --></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="order-detail__summ">
                                            <?php
                                            if ($isBonusCard) {
                                                echo '<ul>';

                                                echo '<li>';
                                                echo 'Итого: ';
                                                echo '<span class="info-count">';
                                                echo sprintf('%0.2f', $cheque['SUM_DISCOUNTED'] - $cheque['PAID_BY_BONUS']);
                                                echo '</span>';
                                                echo 'руб.';
                                                echo '</li>';

                                                echo '<li>';
                                                echo 'Оплачено бонусами: ';
                                                echo '<span class="info-count">';
                                                echo round($cheque['PAID_BY_BONUS'], 2);
                                                echo '</span>';
                                                echo '</li>';

                                                echo '<li>';
                                                echo 'Начислено бонусов за покупку: ';
                                                echo '<span class="info-count">';
                                                echo round($cheque['BONUS'], 2);
                                                echo '</span>';
                                                echo '</li>';

                                                echo '</ul>';
                                            } else {
                                                echo '<div>';
                                                echo 'Итого: ';
                                                echo '<span class="fz24">';
                                                echo sprintf('%0.2f', $cheque['SUM']);
                                                echo '</span>';
                                                echo 'руб.';
                                                echo '</div>';

                                                echo '<div>';
                                                echo 'Итого со скидкой: ';
                                                echo '<span class="fz34">';
                                                echo sprintf('%0.2f', $cheque['SUM_DISCOUNTED']);
                                                echo '</span>';
                                                echo 'руб.';
                                                echo '</div>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </td>
                </tr>
                <?php
            }
        ?>
        </tbody>
    </table>
    <?php
}
