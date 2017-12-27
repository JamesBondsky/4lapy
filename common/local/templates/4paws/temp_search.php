<?php

use FourPaws\Decorators\SvgDecorator;

?>
<div class="b-form-inline b-form-inline--search">
    <form class="b-form-inline__form b-form-inline__form--search js-popover-search">
        <div class="b-input">
            <input class="b-input__input-field"
                   type="text"
                   id="header-search"
                   placeholder="Найти лучшее для вашего питомца…"
                   name="text" />
        </div>
        <button class="b-button b-button--form-inline b-button--search">
            <span class="b-icon">
                <?= new SvgDecorator('icon-search', 16, 16) ?>
            </span>
        </button>
    </form>
    <a class="b-form-inline__mobile-search js-hide-open-menu" href="javascript:void(0)" title="">
        <span class="b-icon b-icon--header-search-mobile">
            <?= new SvgDecorator('icon-search-header', 20, 20) ?>
        </span>
    </a>
    <div class="b-form-inline__autocomplete-wrapper b-form-inline__autocomplete-wrapper--search"
         id="id-header-search-auto">
    </div>
</div>
