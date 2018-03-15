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
$required = true;
$nextUrl = '/ajax/food_selection/show/step/begin/';

/** Тип питомца */
$val = $values['pet_type'];
$sections = $all_sections['pet_type'];
require_once __DIR__.'/pet_type.php';

$nextUrl='/ajax/food_selection/show/step/required/';

/** Возраст питомца */
$nextStep++;
$val = $values['pet_age'];
$sections = $all_sections['pet_age'];
require_once __DIR__.'/pet_age.php';

/** Размер питомца */
if($petTypeCode === 'dog') {
    $nextStep++;
    $val = $values['pet_size'];
    $sections = $all_sections['pet_size'];
    require_once __DIR__ . '/pet_size.php';
}

/** Специализация */
$nextStep++;
$val = $values['food_spec'];
$sections = $all_sections['food_spec'];
require_once __DIR__.'/food_spec.php';

/** Тип корма */
$nextStep++;
$val = $values['food_consistence'];
$sections = $all_sections['food_consistence'];
require_once __DIR__.'/food_consistence.php';

/** не обязательные поля */
if($full_fields){
    $required = false;
    $nextUrl='/ajax/food_selection/show/step/not_required/';

    /** Особенности */
    $nextStep++;
    $val = $values['food_ingridient'];
    $sections = $all_sections['food_ingridient'];
    require_once __DIR__.'/food_ingridient.php';

    /** Вкус */
    $nextStep++;
    $val = $values['food_flavour'];
    $sections = $all_sections['food_flavour'];
    require_once __DIR__.'/food_flavour.php';
}