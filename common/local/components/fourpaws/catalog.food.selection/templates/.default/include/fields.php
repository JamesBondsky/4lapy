<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array  $all_sections
 * @var array  $values
 * @var bool   $full_fields
 * @var string $petTypeCode
 * @var        $this FoodSelectionController
 */

use FourPaws\FoodSelectionBundle\AjaxController\FoodSelectionController;

if (!\is_array($sections) || empty($sections)) {
    return;
}

$nextStep = 1;
$nextUrl = '/ajax/food_selection/show/step/begin/';

/** Тип питомца */
$code = 'pet_type';
$val = (int)$values[$code];
$petType = $val;
$sections = $all_sections[$code]['ITEMS'];
$sectionName = $all_sections[$code]['SECT_NAME'];
require_once __DIR__ . '/' . $code . '.php';

$nextUrl = '/ajax/food_selection/show/step/required/'; ?>
    <div class="b-quest b-quest--step-2 js-quest js-quest--step-2 js-block-required main-quest-block" data-step="2">
        <?php /** Возраст питомца */
        $code = 'pet_age';
        $val = (int)$values[$code];
        $sections = $all_sections[$code]['ITEMS'];
        $sectionName = $all_sections[$code]['SECT_NAME'];
        require_once __DIR__ . '/' . $code . '.php';

        /** Размер питомца */
        if ($petTypeCode === 'dog') {
            $code = 'pet_size';
            $val = (int)$values[$code];
            $sections = $all_sections[$code]['ITEMS'];
            $sectionName = $all_sections[$code]['SECT_NAME'];
            require_once __DIR__ . '/' . $code . '.php';
        }

        /** Специализация */
        $code = 'food_spec';
        $val = (int)$values[$code];
        $sections = $all_sections[$code]['ITEMS'];
        $sectionName = $all_sections[$code]['SECT_NAME'];
        require_once __DIR__ . '/' . $code . '.php';

        /** Тип корма */
        $code = 'food_consistence';
        $val = (int)$values[$code];
        $foodType = $val;
        $sections = $all_sections[$code]['ITEMS'];
        $sectionName = $all_sections[$code]['SECT_NAME'];
        require_once __DIR__ . '/' . $code . '.php'; ?>
    </div>
<?php
/** не обязательные поля */
if ($full_fields) { ?>
    <div class="b-quest b-quest--step-3 js-quest js-quest--step-3 js-block-norequired main-quest-block" data-step="3">
        <?php $required = false;
        $nextUrl = '/ajax/food_selection/show/step/not_required/';
        $nextStep = 3;

        /** Особенности */
        $code = 'food_ingridient';
        $val = (int)$values[$code];
        $sections = $all_sections[$code]['ITEMS'];
        $sectionName = $all_sections[$code]['SECT_NAME'];
        require_once __DIR__ . '/' . $code . '.php';

        /** Вкус */
        $code = 'food_flavour';
        $val = (int)$values[$code];
        $sections = $all_sections[$code]['ITEMS'];
        $sectionName = $all_sections[$code]['SECT_NAME'];
        require_once __DIR__ . '/' . $code . '.php'; ?>
    </div>
    <?php if ($this instanceof FoodSelectionController) { ?>
        <script>
            <?= $this->getDataLayerService()->renderFoodSelection(
                $petType ?? '',
                $foodType ?? '',
                $foodSpecification ?? 'Любой'
            ) ?>
        </script>
    <?php }
}
