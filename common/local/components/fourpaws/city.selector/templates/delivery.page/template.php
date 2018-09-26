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

<p>Доставка и оплата зависит от вашего местоположения, выберите город или населенный пункт, где вы хотите
    получить заказ</p>
<div class="b-delivery__town-form">
    <form class="js-delivery-form" action="<?= $arResult['DELIVERY_INFO_URL'] ?>">
        <div class="b-delivery__town-form--input">
            <input class="b-input__input-field"
                   placeholder="Введите город..."
                   type="text"
                   value="<?= $arResult['SELECTED_CITY']['NAME'] ?>"
                   data-url="<?= $arResult['CITY_AUTOCOMPLETE_URL'] ?>">
        </div>
        <button class="b-button b-button--form-inline b-button--delivery"></button>
    </form>
    <div class="b-delivery__town-form--dropdown">
        <ul></ul>
    </div>
</div>
