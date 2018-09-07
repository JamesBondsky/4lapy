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

$showForm = true;

$errBlock = '<div class="form-page__message b-icon"><i class="icon icon-warning"></i><span class="text-h4 text-icon">%s</span></div>';

echo '<div id="refreshingBlockContainer">';
if (isset($arResult['REGISTRATION_STATUS'])) {
    if ($arResult['REGISTRATION_STATUS'] === 'SUCCESS') {
        $showForm = false;
        ?>
        <h2 class="text-h3">
            <?php
            if ($arResult['PRINT_FIELDS']['phone']['VALUE']) {
                echo 'Создан аккаунт по номеру телефона: '.$arResult['PRINT_FIELDS']['phone']['VALUE'];
            } else {
                echo 'Аккаунт создан';
            }
            ?>
        </h2>
        <br><br>
        <div>
            <a href="javascript:void(0)" data-user-id="<?= $arResult['REGISTERED_USER_ID'] ?>" class="btn inline-block avatarAuth">
                Авторизоваться
            </a>
            <a href="<?= $arParams['CURRENT_PAGE'] ?>" class="btn inline-block">Отказаться</a>
        </div>
        <?php
    }
}

if ($showForm) {
    $attr = '';
    $attr .= ' data-ajax-url="' . $componentPath . '/ajax.php"';
    $attr .= ' data-result-container="#refreshingBlockContainer"';

    $showAuthButton = false;
    ?>
    <form class="form-page mb-l" action=""<?= $attr ?> method="post">
        <div>
            <input type="hidden" name="formName" value="customerRegistration">
            <input type="hidden" name="action" value="postForm">
            <input type="hidden" name="sessid" value="<?= bitrix_sessid() ?>">
            <?php

            if ($arResult['STEP'] >= 1) {
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
                    <label for="<?= $fieldName ?>" class="form-page__label">
                        Мобильный телефон (10 знаков без 7 или 8 в формате 9ХХХХХХХХХ)
                    </label>
                    <input id="<?= $fieldName ?>"
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
                    <label for="<?= $fieldName ?>" class="form-page__label">Фамилия</label>
                    <input id="<?= $fieldName ?>"
                           name="<?= $fieldName ?>"
                           value="<?= $value ?>"<?= $attr ?>
                           class="form-page__field mb-l"
                           type="text">
                    <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
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
                    <label for="<?= $fieldName ?>" class="form-page__label">Имя</label>
                    <input id="<?= $fieldName ?>"
                           name="<?= $fieldName ?>"
                           value="<?= $value ?>"<?= $attr ?>
                           class="form-page__field mb-l"
                           type="text">
                    <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
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
                    <label for="<?= $fieldName ?>" class="form-page__label">Отчество</label>
                    <input id="<?= $fieldName ?>"
                           name="<?= $fieldName ?>"
                           value="<?= $value ?>"<?= $attr ?>
                           class="form-page__field mb-l"
                           type="text">
                    <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
                </div>
                <?php
            
                // Поле: Укажите свой пол
                $fieldName = 'genderCode';
                $fieldMeta = $arResult['PRINT_FIELDS'][$fieldName];
                $value = $fieldMeta['VALUE'];
                $attr = '';
                $optAttr = $fieldMeta['READONLY'] ? ' disabled="disabled"' : '';
                $errMess = '';
                /** @var Bitrix\Main\Error $error */
                $error = $fieldMeta['ERROR'];
                if ($error) {
                    switch ($error->getCode()) {
                        case 'exception':
                            $errMess = $error->getMessage();
                            break;
                        case 'empty':
                        case 'not_valid':
                            $errMess = 'Укажите пол';
                            break;
                        default:
                            $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                            break;
                    }
                }
                $male = $component::EXTERNAL_GENDER_CODE_M;
                $female = $component::EXTERNAL_GENDER_CODE_F;
                ?>
                <div class="form-page__field-wrap">
                    <label for="<?= $fieldName ?>" class="form-page__label">Пол</label>
                    <select name="<?= $fieldName ?>">
                        <option<?=$optAttr?> value="">Укажите пол</option>
                        <option<?= ($value == $male ? ' selected="selected"' : $optAttr) ?> value="<?= $male ?>">Мужской</option>
                        <option<?= ($value == $female ? ' selected="selected"' : $optAttr) ?> value="<?= $female ?>">Женский</option>
                    </select>
                    <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
                </div>
                <?php
            
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
                        default:
                            $errMess = '[' . $error->getCode() . '] ' . $error->getMessage();
                            break;
                    }
                }
            
                ?>
                <div class="form-page__field-wrap">
                    <label for="<?= $fieldName ?>" class="form-page__label">Дата рождения дд.мм.гггг</label>
                    <input id="<?= $fieldName ?>"
                           name="<?= $fieldName ?>"
                           value="<?= $value ?>"<?= $attr ?>
                           class="form-page__field mb-l"
                           type="text">
                    <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
                </div>
                <?php

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
                    switch ($error->getCode()) {
                        case 'exception':
                            $errMess = $error->getMessage();
                            break;
                        case 'not_valid':
                            $errMess = 'E-mail задан некорректно';
                            break;
                        case 'already_registered':
                            $errMess = 'Пользователь с таким e-mail уже есть в системе. Для продолжения введите другой e-mail или очистите поле';
                            break;
                    }
                }
                ?>
                <div class="form-page__field-wrap">
                    <label for="<?= $fieldName ?>" class="form-page__label">
                        Ваш email (поле необязательно для заполнения)
                    </label>
                    <input id="<?= $fieldName ?>"
                       name="<?= $fieldName ?>"
                       value="<?= $value ?>"<?= $attr ?>
                       class="form-page__field mb-l"
                       type="text">
                    <?= ($errMess ? sprintf($errBlock, $errMess) : '') ?>
                </div><?php
            
                // сообщаем компоненту, что пользователя можно регистрировать в случае успешных проверок
                ?><input type="hidden" name="doCustomerRegistration" value="Y"><?
            }
        
            // вывод ошибок регистрации карты
            if (isset($arResult['REGISTRATION_STATUS']) && $arResult['REGISTRATION_STATUS'] === 'ERROR') {
                $errMessages = [];
                foreach ($arResult['ERROR']['REGISTRATION'] as $errCode => $errMsg) {
                    $errMessages[] = $errCode !== '' ? '[' . $errCode . '] ' . $errMsg : $errMsg;
                }
                echo '<div class="form-page__field-wrap">';
                echo sprintf($errBlock, 'Ошибка регистрации пользователя:<br>' . implode('<br>', $errMessages));
                echo '</div>';
            }

            ?>
            <div class="form-page__submit-wrap">
                <?php
                if ($showAuthButton) {
                    ?>
                    <a href="javascript:void(0)" data-user-id="<?= $arResult['REGISTERED_USER_ID'] ?>" class="btn inline-block avatarAuth">
                        Авторизоваться
                    </a>
                    <a href="<?= $arParams['CURRENT_PAGE'] ?>" class="btn inline-block">Отказаться</a>
                    <?php
                } else {
                    $btnText = $arResult['STEP'] >= 2 ? 'Зарегистрировать' : 'ДАЛЕЕ';
                    ?>
                    <input id="ajaxSubmitButton" class="form-page__btn inline-block" type="submit" value="<?= $btnText ?>">
                    <?php
                }
                ?>
            </div>
        </div>
    </form><?php
}

echo '</div>';

if ($arResult['USE_AJAX'] === 'Y' && $arResult['IS_AJAX_REQUEST'] !== 'Y') {
    ?>
    <script data-name="front_office_customer_registration" type="text/javascript">
        $(document).ready(
            function () {
                $('#page').on(
                    'change',
                    '#email',
                    function (ev) {
                        var p = /^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/;
                        if (this.value !== '' && !p.test(this.value)) {
                            ev.preventDefault();
                            alert('В поле Email введены недопустимые символы!');
                        }
                    }
                );
                var body = $('body');
    
                body.on(
                    'click',
                    '#ajaxSubmitButton',
                    function (event) {
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
                            function (i, field) {
                                sendData[field.name] = field.value;
                            }
                        );

                        $.ajax(
                            {
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
                            }
                        );
                    }
                );

                body.on(
                    'click',
                    '.avatarAuth',
                    function (event) {
                        event.preventDefault();
                        var submitButton = $(this);
                        var userId = submitButton.data('user-id');
                        var actionUrl = '<?=\CUtil::JSEscape($arResult['AVATAR_AUTH_PAGE'])?>';
                        if (userId > 0) {
                            $('body').append(
                                '<form action="' + actionUrl + '" method="post" id="avatarForceAuthForm">' +
                                    '<input type="hidden" name="action" value="forceAuth">' +
                                    '<input type="hidden" name="userId" value="' + userId + '">' +
                                '</form>'
                            );
                            $('#avatarForceAuthForm').submit();
                        }
                    }
                );
            }
        );
    </script>
    <?php
}
