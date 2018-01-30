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

if (isset($arResult['REGISTRATION_STATUS'])) {
    if ($arResult['REGISTRATION_STATUS'] === 'SUCCESS') {
        $showForm = false;

        ?><h2 class="text-h3">Поздравляем! Вы успешно зарегистрировали бонусную карту и теперь можете оплачивать покупки бонусами!</h2>
        <h3 class="text-h4 mb-l">Предъявляйте карту при каждом посещении магазина, получайте и накапливайте бонусы. Оплачивайте Ваши покупки бонусами без ограничений!</h3>
        <div><a href="<?=$arParams['CURRENT_PAGE']?>" class="btn inline-block">Следующий клиент</a></div><?php
    }
}

if ($showForm) {
    $attr = '';
    $attr .= ' data-ajax-url="'.$componentPath.'/ajax.php"';
    $attr .= ' data-result-container="#refreshingBlockContainer"';
    ?><form class="form-page mb-l" action=""<?=$attr?>  method="post">
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
                        case 'activated':
                            $errMess = 'Карта уже активирована';
                            break;
                    }
                }
                ?><p class="text-h3 mb-l">Введите штрих-код Вашей карты:</p>
                <div class="form-page__field-wrap">
                    <label for="<?=$fieldName?>" class="form-page__label">Номер вашей карты</label>
                    <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text"><?
                    if ($errMess) {
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
                if ($error) {
                    $errMess = 'Неизвестная ошибка';
                    /** @var Bitrix\Main\Error $error */
                    $error = $arResult['ERROR']['FIELD'][$fieldName];
                    switch ($error->getCode()) {
                        case 'exception':
                            $errMess = $error->getMessage();
                            break;
                        case 'empty':
                            $errMess = 'Как к вам обращаться?';
                            break;
                        case 'not_valid':
                            $errMess = 'Введите корректные данные';
                            break;
                    }
                }
                ?><div class="form-page__field-wrap">
                    <label for="<?=$fieldName?>" class="form-page__label">Фамилия</label>
                    <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text"><?php
                    if ($errMess) {
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
                if ($error) {
                    $errMess = 'Неизвестная ошибка';
                    switch ($error->getCode()) {
                        case 'exception':
                            $errMess = $error->getMessage();
                            break;
                        case 'empty':
                            $errMess = 'Как к Вам обращаться?';
                            break;
                        case 'not_valid':
                            $errMess = 'Введите корректные данные';
                            break;
                    }
                }
                ?><div class="form-page__field-wrap">
                    <label for="<?=$fieldName?>" class="form-page__label">Имя</label>
                    <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text"><?php
                    if ($errMess) {
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
                if ($error) {
                    $errMess = 'Неизвестная ошибка';
                    switch ($error->getCode()) {
                        case 'exception':
                            $errMess = $error->getMessage();
                            break;
                        case 'empty':
                            $errMess = 'Как к Вам обращаться?';
                            break;
                        case 'not_valid':
                            $errMess = 'Введите корректные данные';
                            break;
                    }
                }
                ?><div class="form-page__field-wrap">
                    <label for="<?=$fieldName?>" class="form-page__label">Отчество</label>
                    <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text"><?php
                    if ($errMess) {
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
                if ($error) {
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
                $male = $component::EXTERNAL_GENDER_CODE_M;
                $female = $component::EXTERNAL_GENDER_CODE_F;
                ?><div class="form-page__field-wrap">
                    <select name="<?=$fieldName?>">
                        <option value="">Укажите свой пол</option>
                        <option<?=($value == $male ? ' selected="selected"' : '')?> value="<?=$male?>">Мужской</option>
                        <option<?=($value == $female ? ' selected="selected"' : '')?> value="<?=$female?>">Женский</option>
                    </select><?php
                    if ($errMess) {
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
                if ($error) {
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
                    if ($errMess) {
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
                if ($error) {
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
                    if ($errMess) {
                        echo sprintf($errBlock, $errMess);
                        /*
                    } else {
                        if ($arResult['POSTED_STEP'] >= 3) {
                            ?><div class="form-page__message b-icon">
                                <i class="icon icon-warning-ok"></i>
                                <span class="text-h4 text-icon">Подтверждён</span>
                            </div><?php
                        }
                        */
                    }
                ?></div><?php
            }


            if ($arResult['STEP'] >= 4) {
                // Поле: Ваш email(поле необязательно для заполнения)
                $fieldName = 'email';
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
                    /** @var Bitrix\Main\Error $error */
                    $error = $arResult['ERROR']['FIELD'][$fieldName];
                    switch ($error->getCode()) {
                        case 'exception':
                            $errMess = $error->getMessage();
                            break;
                        case 'not_valid':
                            $errMess = 'E-mail задан некорректно';
                            break;
                        case 'already_registered':
                            $errMess = 'Пользователь с таким e-mail уже есть в системе. Для продолжения активации бонусной карты введите другой e-mail или очистите поле';
                            break;
                    }
                }
                ?><div class="form-page__field-wrap">
                    <label for="<?=$fieldName?>" class="form-page__label">Ваш email (поле необязательно для заполнения)</label>
                    <input id="<?=$fieldName?>" name="<?=$fieldName?>" value="<?=$value?>"<?=$attr?> class="form-page__field mb-l" type="text"><?php
                    if ($errMess) {
                        echo sprintf($errBlock, $errMess);
                    }
                ?></div><?php

                // сообщаем компоненту, что карту можно регистрировать в случае успешных проверок
                ?><input type="hidden" name="doCardRegistration" value="Y"><?
            }

            // вывод ошибок регистрации карты, если есть
            if (isset($arResult['REGISTRATION_STATUS']) && $arResult['REGISTRATION_STATUS'] === 'ERROR') {
                $errMessages = [];
                foreach ($arResult['ERROR']['REGISTRATION'] as $errCode => $errMsg) {
                    $errMessages[] = $errCode !== '' ? '['.$errCode.'] '.$errMsg : $errMsg;
                }
                echo '<div class="form-page__field-wrap">';
                echo sprintf($errBlock, 'Ошибка регистрации карты:<br>'.implode('<br>', $errMessages));
                echo '</div>';
            }

            $btnText = $arResult['STEP'] >= 4 ? 'Зарегистрировать карту' : 'ДАЛЕЕ';
            ?><div class="form-page__submit-wrap">
                <input id="ajaxSubmitButton" class="form-page__btn inline-block" type="submit" value="<?=$btnText?>">
            </div>
        </div>
    </form><?php
}

echo '</div>';


if ($arResult['USE_AJAX'] === 'Y' && $arResult['IS_AJAX_REQUEST'] !== 'Y') {
    ?><script data-name="front_office_card_registration" type="text/javascript">
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
