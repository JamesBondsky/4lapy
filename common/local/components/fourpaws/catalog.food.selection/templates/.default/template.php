<?php
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

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$frame = $this->createFrame(); ?>
<aside class="b-food__aside js-food-permutation-desktop">
    <p class="b-food__text b-food__text--description js-food-permutation-block">Выберите правильный корм. Укажите данные
                                                                                Вашего животного и получите список
                                                                                рекомендованных кормов с учетом
                                                                                особенностей ингредиентов
    </p>
    <a class="b-button b-button--gray b-button--full-width b-button--q-food js-q-food-parameter"
       href="javascript:void(0);"
       title="Изменить параметры">Изменить параметры</a>
    <form class="b-food__form js-food-selection" action="/ajax/food_selection/show/step/pet/type/">
        <div class="js-quest-food">
            <div class="b-quest b-quest--step-1 js-quest js-quest--step-1">
                <h3 class="b-quest__title">Питомец</h3>
                <h4 class="b-quest__subtitle">Тип</h4>
                <? /** @var Sect $item */
                foreach($arResult['PET_TYPES'] as $key => $item){?>
                <div class="b-radio b-radio--q-food">
                    <input class="b-radio__input"
                           type="radio"
                           name="pet_type"
                           value="cat"
                           id="id-quest-type-<?=$key+1?>" />
                    <label class="b-radio__label b-radio__label--q-food"
                           for="id-quest-type-<?=$key+1?>">
                        <span class="b-radio__text-label">Кошка</span>
                    </label>
                </div>
                <?}?>
                <div class="b-radio b-radio--q-food">
                    <input class="b-radio__input"
                           type="radio"
                           name="pet_type"
                           value="dog"
                           id="id-quest-type-2" />
                    <label class="b-radio__label b-radio__label--q-food"
                           for="id-quest-type-2">
                        <span class="b-radio__text-label">Собака</span>
                    </label>
                </div>
            </div>
            <a class="b-button b-button--mobile-show b-button--gray b-button--full-width b-button--q-food js-q-food-show-product"
               href="javascript:void(0);"
               title="Показать товары">Показать товары</a>
        </div>
    </form>
</aside>