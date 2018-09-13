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

// список уже зарегистрированных пользователей с заданным номером телефона
if (isset($arResult['ALREADY_REGISTERED_USERS']) && !empty($arResult['ALREADY_REGISTERED_USERS'])) {
    $showForm = false;
    include __DIR__ . '/inc.user_list.php';
}

// форма запроса номера телефона и регистрационных данных
if ($showForm) {
    include __DIR__ . '/inc.form.php';
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
