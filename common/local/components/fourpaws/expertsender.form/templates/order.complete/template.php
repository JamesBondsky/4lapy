<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Decorators\SvgDecorator;

/**
 * @var array                             $arParams
 * @var array                             $arResult
 * @var array                             $templateData
 * @var FourPawsExpertsenderFormComponent $component
 */

?>
    <div class="js-feedback-permutation">
        <div class="b-feedback__header">Для получения информации по текущему заказу, пожалуйста, заполните почту</div>
        <div class="b-form-inline b-form-inline--feedback">
            <form class="b-form-inline__form b-form-inline__form--feedback js-form-validation js-subscription-query"
                  data-url="/ajax/user/subscribe/subscribe/"
                  onsubmit="<?= $arResult['ON_SUBMIT'] ?>"
                  method="post">
                <?
                if ($arParams['USER_ID']) {
                    ?>
                    <input type="hidden" name="user_id" value="<?= $arParams['USER_ID'] ?>">
                    <?
                }
                ?>
                <input type="hidden" name="type" value="all">
                <div class="b-input">
                    <input class="b-input__input-field b-input__input-field-grey"
                           type="email"
                           name="email"
                           id="feedback"
                           placeholder="Адрес эл. почты"
                           value="<?= $arResult['EMAIL'] ?>" />
                </div>
                <button class="b-button b-button--form-inline b-button--feedback">
                    <span class="b-icon">
                        <?= new SvgDecorator('icon-check', 16, 16) ?>
                    </span>
                </button>
            </form>
        </div>
    </div>
<?php
