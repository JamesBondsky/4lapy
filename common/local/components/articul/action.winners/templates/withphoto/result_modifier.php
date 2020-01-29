<?php
foreach ($arResult as $element) {
    foreach ($element['WINNERS'] as $winner) {
        if ($winner['PROPERTY_SLIDER_VALUE']) {
            $arResult['ITEMS'][] = $winner;
        }
    }
}
