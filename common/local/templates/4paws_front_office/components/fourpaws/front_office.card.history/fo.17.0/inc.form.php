<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain                                     $APPLICATION
 * @var array                                        $arParams
 * @var array                                        $arResult
 * @var FourPawsFrontOfficeCardRegistrationComponent $component
 * @var CBitrixComponentTemplate                     $this
 * @var string                                       $templateName
 * @var string                                       $componentPath
 */

$errBlock =
    '<div class="form-page__message b-icon"><i class="icon icon-warning"></i><span class="text-h4 text-icon">%s</span></div>';

$showForm = true;
if ($showForm) {
    ?>
    <form class="form-page mb-l" action="" method="post">
        <div>
            <input type="hidden" name="formName" value="cardHistory">
            <input type="hidden" name="action" value="postForm">
            <input type="hidden" name="getContactCards" value="Y">
            <input type="hidden" name="getContactCheques" value="Y">
            <input type="hidden" name="sessid" value="<?= bitrix_sessid() ?>"><?php
            
            // Поле: Номер карты
            $fieldName = 'cardNumberForHistory';
            $fieldMeta = $arResult['PRINT_FIELDS'][$fieldName];
            $value     = $fieldMeta['VALUE'];
            $attr      = '';
            $attr      .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr      .= ' maxlength="13"';
            $errMess   = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if ($error) {
                switch ($error->getCode()) {
                    case 'empty':
                        $errMess = 'Пожалуйста, укажите номер карты';
                        break;
                    case 'not_found':
                        $errMess = 'Карта не найдена или невалидна';
                        break;
                    case 'wrong_status':
                        $errMess = 'Некорректный статус карты';
                        break;
                    case 'incorrect_value':
                        $errMess = 'Некорректный номер карты';
                        break;
                    case 'runtime':
                        $errMess = $error->getMessage();
                        break;
                    default:
                        $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                        break;
                }
            }
            ?>
            <div class="form-page__field-wrap">
                <label for="<?= $fieldName ?>" class="form-page__label">Номер карты <sup>*</sup></label>
                <input id="<?= $fieldName ?>"
                       name="<?= $fieldName ?>"
                       value="<?= $value ?>"<?= $attr ?>
                       class="form-page__field mb-l"
                       type="text">
                <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
            </div>
            <?php

            // вывод общих ошибок, если есть
            if (!empty($arResult['ERROR']['EXEC'])) {
                $errMessages = [];
                foreach ($arResult['ERROR']['EXEC'] as $errName => $errMsg) {
                    $errMessages[] = $errName ? '[' . $errName . '] ' . $errMsg : $errMsg;
                }
                echo '<div class="form-page__field-wrap">';
                echo sprintf($errBlock, 'Ошибки запроса данных:<br>' . implode('<br>', $errMessages));
                echo '</div>';
            }

            $btnText = 'Запросить';
            ?>
            <div class="form-page__submit-wrap">
                <input id="ajaxSubmitButton" class="form-page__btn inline-block" type="submit" value="<?= $btnText ?>">
            </div>
        </div>
    </form>
    <?php
}
