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

?><form class="form-page mb-l" action="" method="post">
    <div>
        <input type="hidden" name="formName" value="cardRegistration">
        <input type="hidden" name="action" value="postForm">
        <input type="hidden" name="sessid" value="<?=bitrix_sessid()?>"><?php

        if ($arResult['STEP'] >= 1) {
            // Поле: Номер вашей карты
            $fieldName = 'cardNumber';
            $fieldMeta = $arResult['PRINT_FIELDS'][$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr .= ' maxlength="13"';
            $errMess = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if($error) {
                $errMess = 'Неизвестная ошибка';
                switch ($error->getCode()) {
                    case 'exception':
                        $errMess = $error->getMessage();
                        break;
                    case 'empty':
                        $errMess = 'Пожалуйста, укажите номер карты';
                        break;
                    case 'not_found':
                        $errMess = 'Увы, мы не нашли этой карты :-(';
                        break;
                    case 'activated':
                        $errMess = 'Увы, но карта уже активирована :-(';
                        break;
                }
            }
            ?><p class="text-h3 mb-l">Введите штрих-код Вашей карты:</p>
            <div class="form-page__field-wrap">
                <label for="<?=$fieldName?>" class="form-page__label">Номер вашей карты</label>
                <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text"><?
                if(strlen($errMess)) {
                    echo sprintf($errBlock, $errMess);
                }
            ?></div><?php
        }


        if ($arResult['STEP'] >= 2) {
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
            if($error) {
                $errMess = 'Неизвестная ошибка';
                /** @var Bitrix\Main\Error $error */
                $error = $arResult['ERROR']['FIELD'][$fieldName];
                switch ($error->getCode()) {
                    case 'exception':
                        $errMess = $error->getMessage();
                        break;
                    case 'empty':
                        $errMess = 'Как к Вам обращаться?';
                        break;
                    case 'not_valid':
                        $errMess = 'Поле содержит недопустимые символы';
                        break;
                }
            }
            ?><div class="form-page__field-wrap">
                <label for="<?=$fieldName?>" class="form-page__label">Фамилия</label>
                <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text"><?php
                if(strlen($errMess)) {
                    echo sprintf($errBlock, $errMess);
                }
            ?></div><?php

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
            if($error) {
                $errMess = 'Неизвестная ошибка';
                switch ($error->getCode()) {
                    case 'exception':
                        $errMess = $error->getMessage();
                        break;
                    case 'empty':
                        $errMess = 'Как к Вам обращаться?';
                        break;
                    case 'not_valid':
                        $errMess = 'Поле содержит недопустимые символы';
                        break;
                }
            }
            ?><div class="form-page__field-wrap">
                <label for="<?=$fieldName?>" class="form-page__label">Имя</label>
                <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text"><?php
                if(strlen($errMess)) {
                    echo sprintf($errBlock, $errMess);
                }
            ?></div><?php

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
            if($error) {
                $errMess = 'Неизвестная ошибка';
                switch ($error->getCode()) {
                    case 'exception':
                        $errMess = $error->getMessage();
                        break;
                    case 'empty':
                        $errMess = 'Как к Вам обращаться?';
                        break;
                    case 'not_valid':
                        $errMess = 'Поле содержит недопустимые символы';
                        break;
                }
            }
            ?><div class="form-page__field-wrap">
                <label for="<?=$fieldName?>" class="form-page__label">Отчество</label>
                <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text"><?php
                if(strlen($errMess)) {
                    echo sprintf($errBlock, $errMess);
                }
            ?></div><?php

            // Поле: Укажите свой пол
            $fieldName = 'genderCode';
            $fieldMeta = $arResult['PRINT_FIELDS'][$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $errMess = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if($error) {
                $errMess = 'Неизвестная ошибка';
                switch ($error->getCode()) {
                    case 'exception':
                        $errMess = $error->getMessage();
                        break;
                    case 'empty':
                    case 'not_valid':
                        $errMess = 'Укажите свой пол';
                        break;
                }
            }
            $male = $component::GENDER_CODE_M;
            $female = $component::GENDER_CODE_F;
            ?><div class="form-page__field-wrap">
                <select name="<?=$fieldName?>">
                    <option value="">Укажите свой пол</option>
                    <option<?=($value == $male ? ' selected="selected"' : '')?> value="<?=$male?>">Мужской</option>
                    <option<?=($value == $female ? ' selected="selected"' : '')?> value="<?=$female?>">Женский</option>
                </select><?php
                if(strlen($errMess)) {
                    echo sprintf($errBlock, $errMess);
                }
            ?></div><?php

            // Поле: Дата вашего рождения дд.мм.гггг
            $fieldName = 'birthDay';
            $fieldMeta = $arResult['PRINT_FIELDS'][$fieldName];
            $value = $fieldMeta['VALUE'];
            $attr = '';
            $attr .= $fieldMeta['READONLY'] ? ' readonly="readonly"' : '';
            $attr .= ' maxlength="10"';
            $errMess = '';
            /** @var Bitrix\Main\Error $error */
            $error = $fieldMeta['ERROR'];
            if($error) {
                $errMess = 'Неизвестная ошибка';
                switch ($error->getCode()) {
                    case 'exception':
                        $errMess = $error->getMessage();
                        break;
                    case 'empty':
                        $errMess = 'Укажите дату рождения!';
                        break;
                    case 'not_valid':
                        $errMess = 'Дата указана в неверном формате';
                        break;
                }
            }

            ?><div class="form-page__field-wrap">
                <label for="<?=$fieldName?>" class="form-page__label">Дата вашего рождения дд.мм.гггг</label>
                <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text"><?php
                if(strlen($errMess)) {
                    echo sprintf($errBlock, $errMess);
                }
            ?></div><?php
        }


        if ($arResult['STEP'] >= 3) {
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
            if($error) {
                $errMess = 'Неизвестная ошибка';
                /** @var Bitrix\Main\Error $error */
                $error = $arResult['ERROR']['FIELD'][$fieldName];
                switch ($error->getCode()) {
                    case 'exception':
                        $errMess = $error->getMessage();
                        break;
                    case 'empty':
                        $errMess = 'Не хотите с нами разговаривать?';
                        break;
                    case 'not_valid':
                        $errMess = 'Телефон задан в неверном формате';
                        break;
                    case 'already_registered':
                        $errMess = 'Телефон уже зарегистрирован. Обратитесь на горячую линию';
                        break;
                }
            }
            ?><div class="form-page__field-wrap">
                <label for="<?=$fieldName?>" class="form-page__label">Мобильный телефон (10 знаков без 7 или 8 в формате 9ХХХХХХХХХ)</label>
                <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text"><?php
                if(strlen($errMess)) {
                    echo sprintf($errBlock, $errMess);
                } else {
                    if ($arResult['POSTED_STEP'] >= 3) {
                        ?><div class="form-page__message b-icon">
                            <i class="icon icon-warning-ok"></i>
                            <span class="text-h4 text-icon">Подтверждён</span>
                        </div><?php
                    }
                }
            ?></div><?php
        }

        ?><div class="form-page__submit-wrap">
            <input class="form-page__btn inline-block" type="submit" value="ДАЛЕЕ">
        </div>
    </div>
</form><?php
