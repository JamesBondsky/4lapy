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

$errBlock = '<div class="form-page__message b-icon"><i class="icon icon-warning"></i><span class="text-h4 text-icon">%s</span></div>';

$showForm = true;
if ($showForm) {
    ?>
    <form class="form-page mb-l" action="" method="post">
        <div>
            <input type="hidden" name="formName" value="avatar">
            <input type="hidden" name="action" value="postForm">
            <input type="hidden" name="getUsersList" value="Y">
            <input type="hidden" name="sessid" value="<?=bitrix_sessid()?>"><?php

            echo '<p class="text-h3 mb-l">Заполните любое из полей для поиска пользователя:</p>';

            // Поле: Номер карты
            $fieldName = 'cardNumber';
            $fieldMeta = $arResult['PRINT_FIELDS'][$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr .= ' maxlength="13"';
            $errMess = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if ($error) {
                switch ($error->getCode()) {
                    case 'empty':
                        $errMess = 'Пожалуйста, укажите номер карты';
                        break;
                    case 'not_valid':
                        $errMess = 'Номер карты задан в неверном формате';
                        break;
                    case 'runtime':
                        $errMess = $error->getMessage();
                        break;
                    default:
                        $errMess = '['.$error->getCode().'] '.$error->getMessage();
                        break;
                }
            }
            ?>
            <div class="form-page__field-wrap">
                <label for="<?=$fieldName?>" class="form-page__label">Номер карты</label>
                <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text">
                <?=($errMess ? sprintf($errBlock, $errMess) : '')?>
            </div>
            <?php

            // Поле: Мобильный телефон (10 знаков без 7 или 8 в формате 9ХХХХХХХХХ)
            $fieldName = 'phone';
            $fieldMeta = $arResult['PRINT_FIELDS'][$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr .= ' maxlength="10"';
            $errMess = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if ($error) {
                $errMess = 'Неизвестная ошибка';
                /** @var Bitrix\Main\Error $error */
                $error = $arResult['ERROR']['FIELD'][$fieldName];
                switch ($error->getCode()) {
                    case 'empty':
                        $errMess = 'Пожалуйста, укажите номер телефона';
                        break;
                    case 'not_valid':
                        $errMess = 'Телефон задан в неверном формате';
                        break;
                    case 'runtime':
                        $errMess = $error->getMessage();
                        break;
                    default:
                        $errMess = '['.$error->getCode().'] '.$error->getMessage();
                        break;
                }
            }
            ?>
            <div class="form-page__field-wrap">
                <label for="<?=$fieldName?>" class="form-page__label">Мобильный телефон (10 знаков без 7 или 8 в формате 9ХХХХХХХХХ)</label>
                <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text">
                <?=($errMess ? sprintf($errBlock, $errMess) : '')?>
            </div>
            <?php

            // Поле: Фамилия
            $fieldName = 'lastName';
            $fieldMeta = $arResult['PRINT_FIELDS'][$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr .= ' maxlength="100"';
            $errMess = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if ($error) {
                switch ($error->getCode()) {
                    case 'empty':
                        $errMess = 'Поле не заполнено';
                        break;
                    case 'not_valid':
                        $errMess = 'Введите корректные данные';
                        break;
                    case 'runtime':
                        $errMess = $error->getMessage();
                        break;
                    default:
                        $errMess = '['.$error->getCode().'] '.$error->getMessage();
                        break;
                }
            }
            ?>
            <div class="form-page__field-wrap">
                <label for="<?=$fieldName?>" class="form-page__label">Фамилия</label>
                <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text">
                <?=($errMess ? sprintf($errBlock, $errMess) : '')?>
            </div>
            <?php

            // Поле: Имя
            $fieldName = 'firstName';
            $fieldMeta = $arResult['PRINT_FIELDS'][$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr .= ' maxlength="100"';
            $errMess = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if ($error) {
                switch ($error->getCode()) {
                    case 'empty':
                        $errMess = 'Поле не заполнено';
                        break;
                    case 'not_valid':
                        $errMess = 'Введите корректные данные';
                        break;
                    case 'runtime':
                        $errMess = $error->getMessage();
                        break;
                    default:
                        $errMess = '['.$error->getCode().'] '.$error->getMessage();
                        break;
                }
            }
            ?>
            <div class="form-page__field-wrap">
                <label for="<?=$fieldName?>" class="form-page__label">Имя</label>
                <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text">
                <?=($errMess ? sprintf($errBlock, $errMess) : '')?>
            </div>
            <?php

            // Поле: Отчество
            $fieldName = 'secondName';
            $fieldMeta = $arResult['PRINT_FIELDS'][$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr .= ' maxlength="100"';
            $errMess = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if ($error) {
                $errMess = 'Неизвестная ошибка';
                switch ($error->getCode()) {
                    case 'empty':
                        $errMess = 'Поле не заполнено';
                        break;
                    case 'not_valid':
                        $errMess = 'Введите корректные данные';
                        break;
                    case 'runtime':
                        $errMess = $error->getMessage();
                        break;
                    default:
                        $errMess = '['.$error->getCode().'] '.$error->getMessage();
                        break;
                }
            }
            ?>
            <div class="form-page__field-wrap">
                <label for="<?=$fieldName?>" class="form-page__label">Отчество</label>
                <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text">
                <?=($errMess ? sprintf($errBlock, $errMess) : '')?>
            </div>
            <?php

            // Поле: Дата рождения дд.мм.гггг
            $fieldName = 'birthDay';
            $fieldMeta = $arResult['PRINT_FIELDS'][$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr .= ' maxlength="10"';
            $errMess = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if ($error) {
                $errMess = 'Неизвестная ошибка';
                switch ($error->getCode()) {
                    case 'empty':
                        $errMess = 'Поле не заполнено';
                        break;
                    case 'not_valid':
                        $errMess = 'Дата указана в неверном формате';
                        break;
                    case 'runtime':
                        $errMess = $error->getMessage();
                        break;
                    default:
                        $errMess = '['.$error->getCode().'] '.$error->getMessage();
                        break;
                }
            }
            ?>
            <div class="form-page__field-wrap">
                <label for="<?=$fieldName?>" class="form-page__label">Дата рождения дд.мм.гггг</label>
                <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text">
                <?=($errMess ? sprintf($errBlock, $errMess) : '')?>
            </div>
            <?php


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

            $btnText = 'Поиск';
            ?><div class="form-page__submit-wrap">
                <input id="ajaxSubmitButton" class="form-page__btn inline-block" type="submit" value="<?=$btnText?>">
            </div>
        </div>
    </form>
    <?php
}
