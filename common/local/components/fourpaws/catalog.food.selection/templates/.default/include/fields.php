<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15.03.18
 * Time: 16:09
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $all_sections
 * @var array $values
 * @var bool $full_fields
 * @var string $petTypeCode
 */
if (!\is_array($sections) || empty($sections)) {
    return;
}

$nextStep=1;
$nextUrl = '/ajax/food_selection/show/step/begin/';

/** Тип питомца */
$val = (int)$values['pet_type'];
$sections = $all_sections['pet_type'];
require_once __DIR__.'/pet_type.php';

$nextUrl='/ajax/food_selection/show/step/required/';  ?>
<div class="b-quest b-quest--step-2 js-quest js-quest--step-2 js-block-required" data-step="2">
    <?php /** Возраст питомца */
    $val = (int)$values['pet_age'];
    $sections = $all_sections['pet_age'];
    require_once __DIR__.'/pet_age.php';

    /** Размер питомца */
    if($petTypeCode === 'dog') {
        $val = (int)$values['pet_size'];
        $sections = $all_sections['pet_size'];
        require_once __DIR__ . '/pet_size.php';
    }

    /** Специализация */
    $val = (int)$values['food_spec'];
    $sections = $all_sections['food_spec'];
    require_once __DIR__.'/food_spec.php';

    /** Тип корма */
    $val = (int)$values['food_consistence'];
    $sections = $all_sections['food_consistence'];
    require_once __DIR__.'/food_consistence.php'; ?>
</div>
<?php
/** не обязательные поля */
if($full_fields){ ?>
    <div class="b-quest b-quest--step-3 js-quest js-quest--step-3 js-block-norequired" data-step="3">
        <?php $required = false;
        $nextUrl='/ajax/food_selection/show/step/not_required/';
        $nextStep=3;

        /** Особенности */
        $val = (int)$values['food_ingridient'];
        $sections = $all_sections['food_ingridient'];
        require_once __DIR__.'/food_ingridient.php';

        /** Вкус */
        $val = (int)$values['food_flavour'];
        $sections = $all_sections['food_flavour'];
        require_once __DIR__.'/food_flavour.php';  ?>
    </div>
<?php }