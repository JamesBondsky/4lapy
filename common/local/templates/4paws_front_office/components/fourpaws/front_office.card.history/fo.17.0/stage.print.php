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
 * @var string $templateFolder
 */

if ($arResult['CAN_ACCESS'] !== 'Y') {
    ShowError('При обработке запроса произошла ошибка: отказано в доступе');
    return;
}

// логотип, выводимый при печати
echo '<div class="print-ver-logo"><img src="'.$templateFolder.'/images/header_img.png" alt=""></div>';

echo '<div class="cheques-list-print-title text-h3">Списание и начисление бонусов по карте: '.$arResult['PRINT_FIELDS']['cardNumberForHistory']['VALUE'].'</div>';

if (empty($arResult['CHEQUES'])) {
    echo '<p>Нет данных</p>';
    return;
}

?>
<ul class="cheques-list-print-switch">
    <li rel="_prev_month"><span>Прошлый месяц</span></li>
    <li rel="_cur_month"><span>Текущий месяц</span></li>
    <li rel="_prev_week"><span>Прошлая неделя</span></li>
    <li rel="_cur_week"><span>Текущая неделя</span></li>
    <li class="selected" rel="_all"><span>Все</span></li>
</ul>

<table class="cheques-list-print">
    <thead>
    <tr>
        <th class="cheques-list-print__date">Дата</th>
        <th class="cheques-list-print__debit">Списано</th>
        <th class="cheques-list-print__credit">Начислено</th>
    </tr>
    </thead>
    <tbody>
    <?php
        foreach ($arResult['CHEQUES'] as $cheque) {
            /** @var \DateTimeImmutable $chequeDate */
            $chequeDate = $cheque['DATE'];
            $curClass = '';
            $curClass .= ' _all';
            $curClass .= $cheque['IS_CUR_MONTH'] === 'Y' ? ' _cur_month' : '';
            $curClass .= $cheque['IS_CUR_WEEK'] === 'Y' ? ' _cur_week' : '';
            $curClass .= $cheque['IS_PREV_WEEK'] === 'Y' ? ' _prev_week' : '';
            $curClass .= $cheque['IS_PREV_MONTH'] === 'Y' ? ' _prev_month' : '';
            ?>
            <tr class="<?=$curClass?>">
                <td><?=$chequeDate->format('Y-m-d')?></td>
                <td><?=sprintf('-%0.3f', $cheque['PAID_BY_BONUS'])?></td>
                <td><?=sprintf('%0.3f', $cheque['BONUS'])?></td>
            </tr>
            <?php
        }
    ?>
    </tbody>
</table>

<br><br>
<button onclick="window.print();" class="b-button form-page__btn noprint">Напечатать</button>

<script data-name="front_office_card_history_print" type="text/javascript">
    $(document).ready(
        function() {
            $('body').on(
                'click',
                'ul.cheques-list-print-switch span',
                function(event) {
                    event.preventDefault();
                    var switchItem = $(this).closest('li');
                    switchChequesHistoryList(switchItem);
                }
            );
            var switchChequesHistoryList = function(switchItem) {
                var switchContainer = switchItem.closest('ul');
                if (!switchItem.hasClass('selected')) {
                    var listCont = $('table.cheques-list-print tbody');
                    var searchClass = switchItem.attr('rel');
                    $('li', switchContainer).removeClass('selected');
                    switchItem.addClass('selected');
                    $('tr', listCont).addClass('_hidden');
                    $('tr.'+searchClass, listCont).removeClass('_hidden');
                }
            };
        }
    );
</script>
