<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
if (!\is_array($arResult['QUESTIONS']) || empty($arResult['QUESTIONS'])) {
    return;
}

$fieldSet      = [];
$fieldSetOrder = [
    'site_convenience' => '(Вам нравятся дизайн и оформление? Удобно ли пользоваться – находить нужные товары, оформлять заказы? Какие трудности возникли при работе с сайтом? Что вам лично хотелось бы на сайте изменить?)',
    'callcenter' => '(Как быстро с вами связались после оформления заказа? После общения с оператором у вас остались вопросы по товару или условиям доставки и оплаты? Поблагодарили ли вас за оформленный заказ в конце разговора?)',
    'delivery' => '(Курьер позвонил за час до приезда и предупредил о своем визите? Устроил ли вас его внешний вид и манера общения? Поднял ли сотрудник заказ на этаж, если это было необходимо? Вы получили кассовый чек?)',
    'assortment' => '(Вы нашли все, что искали? Весь товар вам удалось заказать в нужном количестве? Устраивают ли цены?)',
    'impression' => '(Вам все понравилось? Порекомендуете ли вы обратиться в нашу компанию друзьям и знакомым? Если у вас есть предложения (пожелания) – напишите их здесь, нам очень важно знать ваше мнение!)',
];
foreach ($fieldSetOrder as $code => $message) {
    $rateCode              = $code . '_rate';
    $comm                  = $arResult['QUESTIONS'][$code];
    $comm['INPUT_NAME']    = 'form_' . $comm['STRUCTURE'][0]['FIELD_TYPE'] . '_' . $comm['STRUCTURE'][0]['ID'];
    $comm['PRINT_MESSAGE'] = $message;
    $radio                 = $arResult['QUESTIONS'][$rateCode];
    $radio['INPUT_NAME']   = 'form_radio_' . $rateCode;
    $fieldSet[]            = [
        'RATE' => $radio,
        'COMMENT' => $comm,
    ];
}
$arResult['FIELD_SET'] = $fieldSet;