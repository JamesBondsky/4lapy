<?

$APPLICATION->IncludeComponent(
    "articul:comparing.list",
    "",
    [
        'TEXT_HEADER' => $arParams['TEXT_HEADER'],
        'TEXT_SELECT_BRAND' => $arParams['TEXT_SELECT_BRAND'],
        'TEXT_SELECT_PRODUCT' => $arParams['TEXT_SELECT_PRODUCT'],
        'TEXT_BUTTON' => $arParams['TEXT_BUTTON'],
    ],
    $component
);

?>
