<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array $arParams
 * @var array $arResult
 * @var array $templateData
 *
 * @var string $componentPath
 * @var string $templateName
 * @var string $templateFile
 * @var string $templateFolder
 *
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

use FourPaws\Decorators\SvgDecorator;

$this->setFrameMode(true);
?>
<div class="b-container b-container--delivery">
    <div class="b-delivery">
        <h1 class="b-title b-title--h1">Доставка и оплата</h1>
        <div class="b-delivery__town">
            <p>Доставка и оплата зависит от вашего местоположения, выберите город или населенный пункт, где вы хотите
                получить заказ</p>
            <div class="b-delivery__town-form">
                <form class="js-delivery-form" action="<?= $arResult['DELIVERY_INFO_URL'] ?>">
                    <div class="b-delivery__town-form--input">
                        <input class="b-input__input-field"
                               placeholder="Введите город..."
                               type="text"
                               data-url="<?= $arResult['CITY_AUTOCOMPLETE_URL'] ?>">
                    </div>
                    <button class="b-button b-button--form-inline b-button--search b-button--delivery"></button>
                </form>
                <div class="b-delivery__town-form--dropdown">
                    <ul></ul>
                </div>
            </div>
        </div>
    </div>
</div>
