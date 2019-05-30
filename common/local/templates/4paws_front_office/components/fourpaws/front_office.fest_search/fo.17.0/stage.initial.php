<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

/*if ($arResult['CAN_ACCESS'] !== 'Y') {
    ShowError('При обработке запроса произошла ошибка: отказано в доступе');
    return;
}*/

/*if ($arResult['IS_AVATAR_AUTHORIZED'] === 'Y') {
    echo '<br><p>Вы уже находитесь в режиме "аватар". <a href="'.$arParams['LOGOUT_URL'].'">Выйти из режима</a>.</p>';
    return;
}*/

if ($arResult['IS_AJAX_REQUEST'] !== 'Y') {
    echo '<div id="refreshingBlockContainer">';
}

// форма
include __DIR__ . '/inc.form.php';

if ($arResult['IS_AJAX_REQUEST'] !== 'Y') {
    echo '</div>';
}

if ($arResult['USE_AJAX'] === 'Y' && $arResult['IS_AJAX_REQUEST'] !== 'Y') {
    ?>
    <script data-name="front_office_fest_search" type="text/javascript">
        var festSearchComponent = new FourPawsFrontOfficeFestSearchComponent(
            {
                siteId: '<?=\CUtil::JSEscape(SITE_ID)?>',
                siteTemplateId: '<?=\CUtil::JSEscape(SITE_TEMPLATE_ID)?>',
                componentPath: '<?=\CUtil::JSEscape($componentPath)?>',
                template: '<?=\CUtil::JSEscape($arResult['JS']['signedTemplate'])?>',
                parameters: '<?=\CUtil::JSEscape($arResult['JS']['signedParams'])?>',
                sessid: '<?=\CUtil::JSEscape(bitrix_sessid())?>',
                containerSelector: '#refreshingBlockContainer'
            }
        );

        festSearchComponent.limitNumberLength();

        $(document).ready(
            function () {
                function isJson(str) {
                    try {
                        JSON.parse(str);
                    } catch (e) {
                        return false;
                    }
                    return true;
                }

                // поиск участника
                $(festSearchComponent.containerSelector).on(
                    'click',
                    '#ajaxSubmitButton',
                    function (event) {
                        event.preventDefault();

                        var submitButton = $(this);
                        var submitForm = submitButton.closest('form');
                        submitButton.attr('disabled', true);
                        submitForm.find('.form-page__submit-wrap').addClass('loading');

                        var formData = submitForm.serializeArray();
                        var sendData = {};
                        $.each(
                            formData,
                            function (i, field) {
                                sendData[field.name] = field.value;
                            }
                        );

                        festSearchComponent.sendRequest(
                            sendData,
                            {
                                callbackComplete: function (jqXHR, textStatus, component) {
                                    if (isJson(jqXHR.responseText)) {
                                        var json = JSON.parse(jqXHR.responseText);
                                        if (json.success === 'Y') {
                                            $('.form-page').find("input[type=text], input[type=number], textarea").val("");
                                            $('.js-update-result-message').remove();
                                            $('[data-name="festUserSearch"]').prepend('<div class="form-page__message js-update-result-message"><i class="icon icon-warning-ok"></i><span class="text-h4 text-icon">' + json.message + '</span></div>');
                                            $('[data-name="festUserUpdate"]').remove(); // костыльно, лучше было бы в php
                                            $('html, body').animate(
                                                {
                                                    scrollTop: 0
                                                },
                                                200
                                            );
                                        } else {
                                            $('.js-update-result-message').remove();
                                            if (!json.message) {
                                                json.message = 'Произошла ошибка';
                                            }
                                            submitForm.find('.form-page__submit-wrap').before('<div class="form-page__message b-icon js-update-result-message"><i class="icon icon-warning"></i><span class="text-h4 text-icon">' + json.message + '</span></div>');
                                        }
                                        submitButton.removeAttr('disabled');
                                        submitForm.find('.form-page__submit-wrap').removeClass('loading');
                                    } else {
	                                    if (textStatus === 'success') {
	                                        $(component.containerSelector).html(jqXHR.responseText);
	                                        $('html, body').animate(
	                                            {
	                                                scrollTop: $(document).height()
	                                            },
	                                            200
	                                        );
	                                        //submitButton.removeAttr('disabled');
	                                        //submitForm.find('.form-page__submit-wrap').removeClass('loading');
	                                    }
                                    }

                                    festSearchComponent.limitNumberLength();
                                }
                            }
                        );
                    }
                );
            }
        );
    </script>
    <?php
}
