<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @var \CBitrixComponentTemplate         $this
 *
 * @var array                             $arParams
 * @var array                             $arResult
 * @var array                             $templateData
 * @var FourPawsExpertsenderFormComponent $component
 *
 * @var string                            $componentPath
 * @var string                            $templateName
 * @var string                            $templateFile
 * @var string                            $templateFolder
 *
 * @global CMain                          $APPLICATION
 * @global CDatabase                      $DB
 */

?>
<div class="b-account-profile__column b-account-profile__column--bottom">
    <div class="b-account-profile__title b-account-profile__title--small">
        Рассылка
    </div>
    <?php if ($arResult['IS_SUBSCRIBED'] || (!empty($arResult['EMAIL']) && $arResult['CONFIRMED'])) { ?>
        <form class="b-account-profile__form" data-url="/ajax/user/subscribe/subscribe/" id="lk-substribe"
              method="post">
            <input type="hidden" name="email" value="<?= $arResult['EMAIL'] ?>">
            <div class="b-account-profile__subscribe-setting">
                <div class="b-checkbox b-checkbox--agree b-checkbox--account-subscribe">
                    <input class="b-checkbox__input" name="type" id="subscribe-all" type="checkbox"
                           value="all" <?= $arResult['IS_SUBSCRIBED'] ? 'checked="checked"' : '' ?>>
                    <label class="b-checkbox__name b-checkbox__name--agree b-checkbox__name--account-subscribe"
                           for="subscribe-all">
                        <span class="b-checkbox__text">Я хочу получать полезную информацию</span>
                    </label>
                </div>
                <div class="b-error"><span class="js-message"></span></div>
                <button class="b-button b-button--account-subcribe">Применить</button>
            </div>
        </form>
    <?php } else { ?>
        <form class="b-account-profile__form js-form-validation" data-url="/ajax/user/subscribe/subscribe/"
              id="lk-substribe" method="post">
            <input type="hidden" name="type" value="all">
            <div class="b-account-profile__no-subscribe">
                <div class="b-input-line b-input-line--subscribe">
                    <div class="b-input-line__label-wrapper">
                        <label class="b-input-line__label" for="email-subscribe">Эл. почта</label>
                    </div>
                    <div class="b-input b-input--registration-form">
                        <input class="b-input__input-field b-input__input-field--registration-form" type="email"
                               id="email-subscribe" name="email" data-url="/ajax/user/subscribe/subscribe/"
                               value="<?= $arResult['EMAIL'] ?>"/>
                        <div class="b-error"><span class="js-message"></span></div>
                    </div>
                </div>
                <button class="b-button b-button--account-subcribe b-button--full-width">Подписаться</button>
            </div>
        </form>
    <?php } ?>
</div>
