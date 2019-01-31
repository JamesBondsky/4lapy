<?
$APPLICATION->IncludeComponent(
    "articul:comparing.detail",
    "",
    [
        'SECTION_ID' => $arResult['VARIABLES']['SECTION_ID'],
        'TEXT_HEADER' => $arParams['TEXT_HEADER'],
    ],
    $component
);
?>