<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arParams
 * @var array                     $arResult
 * @var array                     $templateData
 *
 * @var string                    $componentPath
 * @var string                    $templateName
 * @var string                    $templateFile
 * @var string                    $templateFolder
 *
 * @global CUser                  $USER
 * @global CMain                  $APPLICATION
 * @global CDatabase              $DB
 */

/** @todo
if ($arResult['MODE'] === FourPawsUserComponent::MODE_FORM) { ?>
 * <form name="login_form">
 * <input name="login" type="text" placeholder="Логин"><br>
 * <input name="password" type="password" placeholder="Пароль"><br>
 * <input type="submit" value="Войти">
 * </form>
 * <? foreach ($arResult['socialServices'] as $service) { ?>
 * <?= $service['FORM_HTML'] ?>
 * <? } ?>
 * <div id="result">
 *
 * </div>
 *
 * <script>
 * $(function () {
 * $('input[type="submit"').on('click',
 * function (e) {
 * e.preventDefault();
 *
 * $.ajax({
 * success: function (data) {
 * console.info(data);
 * $('#result').html(data);
 * },
 * url:     '/ajax/user/auth/login/',
 * data:    $(this).parents('form').serialize(),
 * type:    'post'
 * });
 *
 * return false;
 * })
 * })
 * </script>
 * <?php
 *
 * } else {
 * $user = $arResult['user']; ?>
 * <div>
 * <b><?= $user->getName() ?></b> <?= $user->getSecondName() ?> <?= $user->getLastName() ?>
 * <br>
 * <a href="?logout=yes">Выйти</a>
 * </div>
 * <?php } */ ?>
<div class="b-header-info__item b-header-info__item--person">
    <div class="b-header-info__item b-header-info__item--person">
        <a class="b-header-info__link js-open-popover" href="javascript:void(0);" title="Войти">
            <span class="b-icon">
                <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                    <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-person"></use>
                </svg>
            </span>
            <span class="b-header-info__inner">Войти</span>
            <span class="b-icon b-icon--header b-icon--left-3">
                <svg class="b-icon__svg" viewBox="0 0 10 12 " width="10px" height="12px">
                    <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-arrow-down"></use>
                </svg>
            </span>
        </a>
        <div class="b-popover b-popover--person js-popover">
            <div class="b-person">
                <ul class="b-link-block">
                    <li class="b-link-block__item">
                        <a class="b-link-block__link" href="javascript:void(0);" title="Мои заказы">
                            <span class="b-icon">
                                <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                                    <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-order"></use>
                                </svg>
                            </span>
                            Мои заказы
                        </a>
                    </li>
                    <li class="b-link-block__item">
                        <a class="b-link-block__link" href="javascript:void(0);" title="Адреса доставки">
                            <span class="b-icon">
                                <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                                    <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-delivery-header"></use>
                                </svg>
                            </span>
                            Адреса доставки
                        </a>
                    </li>
                    <li class="b-link-block__item">
                        <a class="b-link-block__link" href="javascript:void(0);" title="Мои питомцы">
                            <span class="b-icon">
                                <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                                    <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-pet"></use>
                                </svg>
                            </span>
                            Мои питомцы
                        </a>
                    </li>
                    <li class="b-link-block__item">
                        <a class="b-link-block__link" href="javascript:void(0);" title="Бонусы">
                            <span class="b-icon">
                                <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                                    <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-bonus"></use>
                                </svg>
                            </span>
                            Бонусы
                        </a>
                    </li>
                    <li class="b-link-block__item">
                        <a class="b-link-block__link" href="javascript:void(0);" title="Профиль">
                            <span class="b-icon">
                                <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                                    <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-profile"></use>
                                </svg>
                            </span>
                            Профиль
                        </a>
                    </li>
                    <li class="b-link-block__item">
                        <a class="b-link-block__link" href="javascript:void(0);" title="Выход">
                            <span class="b-icon">
                                <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                                    <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-exit"></use>
                                </svg>
                            </span>
                            Выход
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
