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
            <div class="b-form-inline b-form-inline--search b-form-inline--delivery">
                <form class="b-form-inline__form b-form-inline__form--search b-form-inline__form--delivery js-popover-search">
                    <input class="b-input" type="text" id="header-search" placeholder="Введите город..."/>
                    <button class="b-button b-button--form-inline b-button--search b-button--delivery">
                        <span class="b-icon">
                        <?= new SvgDecorator('icon-search', 16, 16) ?>
                        </span>
                    </button>
                </form>
                <a class="b-form-inline__mobile-search js-open-popover"
                   href="javascript:void(0)"
                   title="">
                    <span class="b-icon b-icon--header-search-mobile">
                        <?= new SvgDecorator('icon-search-heade', 20, 20) ?>
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>
