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

if ($arResult['CAN_ACCESS'] !== 'Y') {
    ShowError('При обработке запроса произошла ошибка: отказано в доступе');
    return;
}

// форма
include __DIR__.'/inc.form.php';

// История покупок
if (!empty($arResult['USERS'])) {

    echo '<div class="lk-container">';
    echo '<div class="tab-users-list">';

    ?>
    <table class="users-list">
        <thead>
            <tr>
                <th>Детали</th>
                <th>Дата покупки</th>
                <th>Адрес магазина</th>
            </tr>
        </thead>
        <tbody>
        <?php
            $rowClass = 'even';
            foreach ($arResult['CHEQUES'] as $cheque) {
                /** @var \DateTimeImmutable $chequeDate */
                $chequeDate = $cheque['DATE'];
                $rowClass = $rowClass === 'even' ? 'odd' : 'even';
                ?>
                <tr class="order-list__head <?=$rowClass?>">
                    <td class="order-list__i">
                        <span class="order-list__dropdown uppercase" data-id="<?=htmlspecialcharsbx($cheque['CHEQUE_ID'])?>">
                            <span>Детали покупки</span>
                        </span>
                    </td>
                    <td class="order-id order-list__dt"><?=$chequeDate->format('d.m.Y H:i:s')?></td>
                    <td class="order-id order-list__address"><?=htmlspecialcharsbx($cheque['BUSINESS_UNIT_NAME'])?></td>
                </tr>

                <tr class="order-list__details <?=$rowClass?>">
                    <td colspan="3" class="order-detail-td">
                        <div data-id="<?=htmlspecialcharsbx($cheque['CHEQUE_ID'])?>" class="order-detail" style="display: none;">
                            <table>
                                <thead>
                                    <tr>
                                        <th class="product-art">Артикул</th>
                                        <th class="product-name">Наименование</th>
                                        <th class="product-quantity">Кол-во</th>
                                        <th class="product-bonus">Начислено бонусов</th>
                                    </tr>
                                </thead>
                                <tbody><!-- cheque details (ajax result) --></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="order-detail__summ">
                                            <?php
                                            if ($isBonusCard) {
                                                echo '<ul>';

                                                echo '<li>';
                                                echo 'Итого: ';
                                                echo '<span class="info-count">';
                                                echo sprintf('%0.2f', $cheque['SUM_DISCOUNTED'] - $cheque['PAID_BY_BONUS']);
                                                echo '</span>';
                                                echo '&nbsp;руб.';
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
                                                echo '&nbsp;руб.';
                                                echo '</div>';

                                                echo '<div>';
                                                echo 'Итого со скидкой: ';
                                                echo '<span class="fz34">';
                                                echo sprintf('%0.2f', $cheque['SUM_DISCOUNTED']);
                                                echo '</span>';
                                                echo '&nbsp;руб.';
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

    echo '</div>';
    echo '</div>';
}
