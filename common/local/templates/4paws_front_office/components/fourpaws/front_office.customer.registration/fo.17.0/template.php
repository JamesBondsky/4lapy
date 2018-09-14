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

echo '<div class="registration-page-cont" id="refreshingBlockContainer">';

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

if ($arResult['PRINT_USER_LIST']) {
    // вывод списка уже зарегистрированных пользователей с заданным номером телефона
    $showForm = false;
    $curUserList = $arResult['PRINT_USER_LIST'];
    include __DIR__ . '/inc.user_list.php';
} elseif ($arResult['PRINT_CONTACT_LIST'] && $arResult['STEP'] <= 2) {
    // вывод списка контактов, найденных в Манзане с заданным номером телефона
    $curUserList = $arResult['PRINT_CONTACT_LIST'];
    include __DIR__ . '/inc.user_list.php';
}

// форма запроса номера телефона и регистрационных данных
if ($showForm) {
    $i = 0;
    foreach ($arResult['PRINT_FIELDS'] as $setKey => $curPrintFields) {
        if ($setKey !== $arResult['SELECTED_CONTACT_ID']) {
            continue;
        }
        $curFormId = 'form'.$i++;
        //$visibleCss = $setKey == $arResult['SELECTED_CONTACT_ID'] ? ' _visible' : '';
        $visibleCss = $arResult['STEP'] == 2 ? '' : ' _visible';
        echo '<div class="registration-form-cont'.$visibleCss.'">';
        include __DIR__ . '/inc.form.php';
        echo '</div>';
    }
}

echo '</div>';

if ($arResult['USE_AJAX'] === 'Y' && $arResult['IS_AJAX_REQUEST'] !== 'Y') {
    ?>
    <script data-name="front_office_customer_registration" type="text/javascript">
        $(document).ready(
            function () {
                $('#page').on(
                    'change',
                    '.form-page ._email',
                    function (ev) {
                        var p = /^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/;
                        if (this.value !== '' && !p.test(this.value)) {
                            ev.preventDefault();
                            alert('В поле Email введены недопустимые символы!');
                        }
                    }
                );
                var body = $('body');

                var isLoading = function() {
                    var actionContainer = $('#refreshingBlockContainer');
                    return actionContainer.hasClass('loading');
                };

                var setLoading = function(val) {
                    var actionContainer = $('#refreshingBlockContainer');
                    if (val === false) {
                        actionContainer.removeClass('loading');
                    } else {
                        actionContainer.addClass('loading');
                    }
                };

                var registerFormSubmit = function(submitForm) {
                    var siteId = '<?=\CUtil::JSEscape(SITE_ID)?>';
                    var siteTemplateId = '<?=\CUtil::JSEscape(SITE_TEMPLATE_ID)?>';
                    var componentPath = '<?=\CUtil::JSEscape($componentPath)?>';
                    var template = '<?=\CUtil::JSEscape($arResult['JS']['signedTemplate'])?>';
                    var parameters = '<?=\CUtil::JSEscape($arResult['JS']['signedParams'])?>';

                    var ajaxUrl = submitForm.data('ajax-url');
                    var resultContainerSelector = submitForm.data('result-container');
                    var submitButton = $('input[type="submit"]', submitForm);

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
                };

                body.on(
                    'click',
                    '.ajaxSubmitButton',
                    function (event) {
                        event.preventDefault();

                        if (isLoading()) {
                            return;
                        }
                        setLoading();

                        var submitForm = $(this).closest('form');
                        registerFormSubmit(submitForm);
                    }
                );

                body.on(
                    'click',
                    '.avatarAuth',
                    function (event) {
                        event.preventDefault();

                        if (isLoading()) {
                            return;
                        }

                        var submitButton = $(this);
                        var userId = submitButton.data('user-id');
                        var actionUrl = '<?=\CUtil::JSEscape($arResult['AVATAR_AUTH_PAGE'])?>';
                        if (userId > 0) {
                            setLoading();
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

                body.on(
                    'click',
                    '.selectRegisterContact',
                    function (event) {
                        event.preventDefault();

                        if (isLoading()) {
                            return;
                        }

                        var submitButton = $(this);
                        var contactId = submitButton.data('contact-id');
                        var actionUrl = '';
                        if (contactId !== '') {
                            var curFormElement = $('form.registration-form').get(0);
                            if (curFormElement) {
                                setLoading();
                                var curForm = $(curFormElement);
                                $('input[name="contactId"]', curForm).val(contactId);
                                registerFormSubmit(curForm);
                            }
                        }
                    }
                );
            }
        );
    </script>
    <?php
}
