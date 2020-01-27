<?php
foreach ($arResult as $element) {
    foreach ($element['WINNERS'] as $winner) {
        $arResult['ITEMS'][] = $winner;
    }
}
