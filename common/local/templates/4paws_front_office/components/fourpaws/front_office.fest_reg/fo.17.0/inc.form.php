<?php

use Bitrix\Main\Error;

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

$requiredMark = '<span style="color: red;">*</span>';

$errBlock =
    '<div class="form-page__message b-icon"><i class="icon icon-warning"></i><span class="text-h4 text-icon">%s</span></div>';
$successBlock =
    '<div class="form-page__message b-icon"><i class="icon icon-warning-ok"></i><span class="text-h4 text-icon">%s</span></div>';

$showForm = true;
if ($showForm) {
    ?>
    <form class="form-page mb-l" action="" method="post">
        <div>
            <input type="hidden" name="formName" value="festUserReg">
            <input type="hidden" name="action" value="userReg">
            <input type="hidden" name="sessid" value="<?= bitrix_sessid() ?>"><?php


	        // Поле: Имя
            $fieldName = 'firstName';
            $fieldId = $fieldName;
            $fieldMeta = $arResult["PRINT_FIELDS"][$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr .= ' maxlength="100"';
            $errMess = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if ($error) {
                switch ($error->getCode()) {
                    case 'exception':
                        $errMess = $error->getMessage();
                        break;
                    case 'empty':
                        $errMess = 'Данные не заданы';
                        break;
                    case 'not_valid':
                        $errMess = 'Введите корректные данные';
                        break;
                    default:
                        $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                        break;
                }
            }
            ?>
            <div class="form-page__field-wrap">
                <label for="<?= $fieldId ?>" class="form-page__label">
                    Имя
                    <?= ($fieldMeta['REQUIRED'] ? $requiredMark : $notRequiredMark) ?>
                </label>
                <input id="<?= $fieldId ?>"
                       name="<?= $fieldName ?>"
                       value="<?= $value ?>"<?= $attr ?>
                       class="form-page__field mb-l"
                       type="text">
                <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
            </div>
	        <?


	        // Поле: Фамилия
            $fieldName = 'lastName';
            $fieldId = $fieldName;
            $fieldMeta = $arResult["PRINT_FIELDS"][$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr .= ' maxlength="100"';
            $errMess = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if ($error) {
                switch ($error->getCode()) {
                    case 'exception':
                        $errMess = $error->getMessage();
                        break;
                    case 'empty':
                        $errMess = 'Данные не заданы';
                        break;
                    case 'not_valid':
                        $errMess = 'Введите корректные данные';
                        break;
                    default:
                        $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                        break;
                }
            }
            ?>
            <div class="form-page__field-wrap">
                <label for="<?= $fieldId ?>" class="form-page__label">
                    Фамилия
                    <?= ($fieldMeta['REQUIRED'] ? $requiredMark : $notRequiredMark) ?>
                </label>
                <input id="<?= $fieldId ?>"
                       name="<?= $fieldName ?>"
                       value="<?= $value ?>"<?= $attr ?>
                       class="form-page__field mb-l"
                       type="text">
                <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
            </div>
	        <?


	        // Поле: Мобильный телефон (10 знаков без 7 или 8 в формате 9ХХХХХХХХХ)
            $fieldName = 'phone';
            $fieldId = $fieldName;
            $fieldMeta = $arResult["PRINT_FIELDS"][$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr .= ' maxlength="10"';
            $errMess = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if ($error) {
                switch ($error->getCode()) {
                    case 'exception':
                        $errMess = $error->getMessage();
                        break;
                    case 'empty':
                        $errMess = 'Не задан номер телефона';
                        break;
                    case 'not_valid':
                        $errMess = 'Телефон задан в неверном формате';
                        break;
                    case 'already_registered':
                        $errMess = 'Данный телефонный номер есть в базе данных сайта, авторизоваться под пользователем?';
                        $showAuthButton = true;
                        break;
                    default:
                        $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                        break;
                }
            }
            ?>
            <div class="form-page__field-wrap">
                <label for="<?= $fieldId ?>" class="form-page__label">
                    Мобильный телефон (10 знаков без 7 или 8 в формате 9ХХХХХХХХХ)
                    <?= ($fieldMeta['REQUIRED'] ? $requiredMark : $notRequiredMark) ?>
                </label>
                <input id="<?= $fieldId ?>"
                       name="<?= $fieldName ?>"
                       value="<?= $value ?>"<?= $attr ?>
                       class="form-page__field mb-l"
                       type="text">
                <?php
                if ($errMess) {
                    echo sprintf($errBlock, $errMess);
                }
                ?>
            </div>
            <?php


            // Поле: Ваш email(поле необязательно для заполнения)
            $fieldName = 'email';
            $fieldId = $fieldName;
            $fieldMeta = $arResult["PRINT_FIELDS"][$fieldName];
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
                    case 'exception':
                        $errMess = $error->getMessage();
                        break;
                    case 'not_valid':
                        $errMess = 'E-mail задан некорректно';
                        break;
                    case 'already_registered':
                        $errMess = 'Пользователь с таким e-mail уже есть в системе.
                        Для продолжения введите другой e-mail или очистите поле';
                        break;
                }
            }
            ?>
            <div class="form-page__field-wrap">
                <label for="<?= $fieldId ?>" class="form-page__label">
                    Ваш email
                    <?= ($fieldMeta['REQUIRED'] ? $requiredMark : $notRequiredMark) ?>
                </label>
                <input id="<?= $fieldId ?>"
                       name="<?= $fieldName ?>"
                       value="<?= $value ?>"<?= $attr ?>
                       class="form-page__field mb-l _email"
                       type="text">
                <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
            </div>
	        <?


	        // Поле: Номер паспорта
            $fieldName = 'passport';
            $fieldId = $fieldName;
            $fieldMeta = $arResult["PRINT_FIELDS"][$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr .= ' maxlength="100"';
            $errMess = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if ($error) {
                switch ($error->getCode()) {
                    case 'exception':
                        $errMess = $error->getMessage();
                        break;
                    case 'empty':
                        $errMess = 'Данные не заданы';
                        break;
                    case 'not_valid':
                        $errMess = 'Введите корректные данные';
                        break;
                    default:
                        $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                        break;
                }
            }
            ?>
            <div class="form-page__field-wrap">
                <label for="<?= $fieldId ?>" class="form-page__label">
                    Номер паспорта
                    <?= ($fieldMeta['REQUIRED'] ? $requiredMark : $notRequiredMark) ?>
                </label>
                <input id="<?= $fieldId ?>"
                       name="<?= $fieldName ?>"
                       value="<?= $value ?>"<?= $attr ?>
                       class="form-page__field mb-l"
                       type="text">
                <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
            </div>
	        <?


            // вывод общих ошибок, если есть
            if (!empty($arResult['ERROR']['EXEC'])) {
                $errMessages = [];
                foreach ($arResult['ERROR']['EXEC'] as $errName => $errMsg) {
                    $errMessages[] =
                        $errName && !in_array($errName, ['emptySearchParams']) ? '[' . $errName . '] '
                                                                                 . $errMsg : $errMsg;
                }
                echo '<div class="form-page__field-wrap">';
                echo sprintf($errBlock, 'Ошибки запроса данных:<br>' . implode('<br>', $errMessages));
                echo '</div>';
            }

            if ($arResult['IS_REGISTERED'] === \Adv\Bitrixtools\Tools\BitrixUtils::BX_BOOL_TRUE) {
                echo '<div class="form-page__field-wrap">';
                echo sprintf($successBlock, 'Участник успешно зарегистрирован, номер: ' . $arResult['PARTICIPANT_ID']);
                echo '</div>';
            }

            $btnText = 'Зарегистрировать';
            ?>
            <div class="form-page__submit-wrap">
                <input id="ajaxSubmitButton" class="form-page__btn inline-block" type="submit" value="<?= $btnText ?>">
	            <p><?= $requiredMark ?>&nbsp;&mdash;&nbsp;обязательное поле</p>
            </div>
        </div>
    </form>
    <?php
}
