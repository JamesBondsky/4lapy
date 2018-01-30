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

$showForm = true;

$errBlock = '<div class="form-page__message b-icon"><i class="icon icon-warning"></i><span class="text-h4 text-icon">%s</span></div>';

echo '<div id="refreshingBlockContainer">';

if ($showForm) {
    $attr = '';
    $attr .= ' data-ajax-url="'.$componentPath.'/ajax.php"';
    $attr .= ' data-result-container="#refreshingBlockContainer"';
    ?><form class="form-page mb-l" action=""<?=$attr?>  method="post">
        <div>
            <input type="hidden" name="formName" value="cardHistory">
            <input type="hidden" name="action" value="postForm">
            <input type="hidden" name="getCardHistory" value="Y">
            <input type="hidden" name="sessid" value="<?=bitrix_sessid()?>"><?php

            // Поле: Номер карты
            $fieldName = 'cardNumberForHistory';
            $fieldMeta = $arResult['PRINT_FIELDS'][$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr .= ' maxlength="16"';
            $errMess = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if ($error) {
                $errMess = 'Неизвестная ошибка';
                switch ($error->getCode()) {
                    case 'exception':
                        $errMess = $error->getMessage();
                        break;
                    case 'empty':
                        $errMess = 'Пожалуйста, укажите номер карты';
                        break;
                    case 'not_found':
                        $errMess = 'Карта не найдена или невалидна';
                        break;
                    case 'wrong_status':
                        $errMess = 'Некорректный статус карты';
                        break;
                }
            }
            ?><div class="form-page__field-wrap">
                <label for="<?=$fieldName?>" class="form-page__label">Номер карты <sup>*</sup></label>
                <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text"><?
                if ($errMess) {
                    echo sprintf($errBlock, $errMess);
                }
            ?></div><?php

            // вывод общих ошибок, если есть
            if (!empty($arResult['ERROR']['EXEC'])) {
                $errMessages = [];
                foreach ($arResult['ERROR']['EXEC'] as $errName => $errMsg) {
                    $errMessages[] = $errName ? '['.$errName.'] '.$errMsg : $errMsg;
                }
                echo '<div class="form-page__field-wrap">';
                echo sprintf($errBlock, 'Ошибки запроса данных:<br>'.implode('<br>', $errMessages));
                echo '</div>';
            }

            $btnText = 'Запросить';
            ?><div class="form-page__submit-wrap">
                <input id="ajaxSubmitButton" class="form-page__btn inline-block" type="submit" value="<?=$btnText?>">
            </div>
        </div>
    </form><?php
}

// Баланс карты
if (!empty($arResult['CURRENT_CARD'])) {
    echo '<br>';
    echo '<p class="abh">Активный баланс: '.$arResult['CURRENT_CARD']['BALANCE'].'&nbsp;баллов</p>';
}

// История покупок
if (!empty($arResult['CHEQUES'])) {
    $isBonusCard = $arResult['CURRENT_CARD'] && $arResult['CURRENT_CARD']['IS_BOUNUS_CARD'] === 'Y';
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
            foreach ($arResult['CHEQUES'] as $key => $cheque) {
                ?>
                <tr<?= ($key % 2 == 1 ? ' class="two"' : '') ?>>
                    <td class="order-list__i">
                        <span class="order-list__dropdown uppercase" data-id="<?=$cheque['CHEQUE_ID']?>">
                            <span>Детали покупки</span>
                        </span>
                    </td>
                    <td class="order-id order-list__dt"><?=$cheque['DATE']->format('d.m.Y H:i:s')?></td>
                    <td class="order-id order-list__address"><?=$cheque['BUSINESS_UNIT_NAME']?></td>
                </tr>

                <tr>
                    <td colspan="5" class="order-detail-td">
                        <div class="order-detail" style="display: none;">
                            <table>
                                <thead>
                                    <tr>
                                        <th colspan="3" class="product-name">Наименование</th>
                                        <th>Кол-во</th>
                                        <th>Начислено бонусов</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="order-detail__summ">
                                            <?php
                                            if ($isBonusCard) {
                                                echo '<ul>';

                                                echo '<li>';
                                                echo 'Итого: ';
                                                echo '<span class="info-count">';
                                                echo sprintf('%0.2f', $cheque['SUMM_DISCOUNTED'] - $cheque['PAID_BY_BONUS']);
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
                                                echo sprintf('%0.2f', $cheque['SUMM']);
                                                echo '</span>';
                                                echo 'руб.';
                                                echo '</div>';

                                                echo '<div>';
                                                echo 'Итого со скидкой: ';
                                                echo '<span class="fz34">';
                                                echo sprintf('%0.2f', $cheque['SUMM_DISCOUNTED']);
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

echo '</div>';


if ($arResult['USE_AJAX'] === 'Y' && $arResult['IS_AJAX_REQUEST'] !== 'Y') {
    ?><script data-name="front_office_card_history" type="text/javascript">
        $(document).ready(
            function() {
                $('body').on(
                    'click',
                    '#ajaxSubmitButton',
                    function(event) {
                        event.preventDefault();

                        var siteId = '<?=\CUtil::JSEscape(SITE_ID)?>';
                        var siteTemplateId = '<?=\CUtil::JSEscape(SITE_TEMPLATE_ID)?>';
                        var componentPath = '<?=\CUtil::JSEscape($componentPath)?>';
                        var template = '<?=\CUtil::JSEscape($arResult['JS']['signedTemplate'])?>';
                        var parameters = '<?=\CUtil::JSEscape($arResult['JS']['signedParams'])?>';

                        var submitButton = $(this);
                        var submitForm = submitButton.closest('form');
                        var ajaxUrl = submitForm.data('ajax-url');
                        var resultContainerSelector = submitForm.data('result-container');

                        submitButton.attr('disabled', true);
                        submitForm.find('.form-page__submit-wrap').addClass('loading');

                        var formData = submitForm.serializeArray();
                        var sendData = {
                            'ajaxContext': {
                                'siteId': siteId,
                                'siteTemplateId': siteTemplateId,
                                'componentPath': componentPath,
                                'template': template,
                                'parameters': parameters
                            }
                        };

                        $.each(
                            formData,
                            function(i, field) {
                                sendData[field.name] = field.value;
                            }
                        );

                        $.ajax({
                            type: 'POST',
                            dataType: 'html',
                            url: ajaxUrl,
                            data: sendData,
                            error: function(x, e) {
                                alert('Error ' + x.status);
                            },
                            complete: function(xhr, status) {
                                $(resultContainerSelector).replaceWith(xhr.responseText);
                                $('html, body').animate(
                                    {
                                        scrollTop: $(document).height()
                                    },
                                    200
                                );
                                submitButton.removeAttr('disabled');
                                submitForm.find('.form-page__submit-wrap').removeClass('loading');
                            }
                        });
                    }
                )
            }
        );
    </script><?php
}
