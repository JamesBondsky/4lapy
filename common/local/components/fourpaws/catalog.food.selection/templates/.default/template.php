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

if (!\is_array($arResult['PET_TYPES']) || empty($arResult['PET_TYPES'])) {
    return;
} ?>
<aside class="b-food__aside js-food-permutation-desktop">
    <p class="b-food__text b-food__text--description js-food-permutation-block">Выберите правильный корм. Укажите данные
        Вашего животного и получите список
        рекомендованных кормов с учетом
        особенностей ингредиентов
    </p>
    <a class="b-button b-button--gray b-button--full-width b-button--q-food js-q-food-parameter"
       href="javascript:void(0);"
       title="Изменить параметры">Изменить параметры</a>
    <form class="b-food__form js-food-selection">
        <div class="js-quest-food">
            <?php $sections = $arResult['PET_TYPES'];
            $nextUrl = '/ajax/food_selection/show/step/begin/';
            $required = true;
            $nextStep = 1;
            require_once __DIR__ . '/include/pet_type.php'; ?>
            <a class="b-button b-button--mobile-show b-button--gray b-button--full-width b-button--q-food js-q-food-show-product"
               href="javascript:void(0);"
               title="Показать товары">Показать товары</a>
        </div>
    </form>
</aside>
<main class="b-food__main b-food__main--q-food js-quest js-quest--step-final" data-url="/ajax/food_selection/show/step/not_required/" role="main"></main>