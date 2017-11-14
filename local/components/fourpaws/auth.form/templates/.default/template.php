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

?>
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
        <?php if ($arResult['MODE'] === FourPawsUserComponent::MODE_FORM) {
            <<<example
    <form name="login_form">
        <input name="login" type="text" placeholder="Логин"><br>
        <input name="password" type="password" placeholder="Пароль"><br>
        <input type="submit" value="Войти">
    </form>
example;
            /* example
            <? foreach ($arResult['socialServices'] as $service) { ?>
                <?= $service['FORM_HTML'] ?>
            <? } ?>
            */ ?>
            
            <?php
        } else {
            $user = $arResult['user']; ?>
            <?php $APPLICATION->IncludeComponent('bitrix:menu',
                                                 'header.personal_menu',
                                                 [
                                                     'COMPONENT_TEMPLATE'    => 'header.personal_menu',
                                                     'ROOT_MENU_TYPE'        => 'personal',
                                                     'MENU_CACHE_TYPE'       => 'A',
                                                     'MENU_CACHE_TIME'       => '360000',
                                                     'MENU_CACHE_USE_GROUPS' => 'N',
                                                     'MENU_CACHE_GET_VARS'   => [],
                                                     'MAX_LEVEL'             => '1',
                                                     'CHILD_MENU_TYPE'       => '',
                                                     'USE_EXT'               => 'N',
                                                     'DELAY'                 => 'N',
                                                     'ALLOW_MULTI_SELECT'    => 'N',
                                                 ],
                                                 false,
                                                 ['HIDE_ICONS' => 'Y']); ?>
        <?php } ?>
    </div>
</div>
