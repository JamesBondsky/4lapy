<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var FourPawsFrontOfficeCardRegistrationComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

if ($arResult['CAN_ACCESS'] !== 'Y') {
    // т.к. результат вставляется в тело таблицы, то об ошибке сообщаем через статус
    if ($arResult['IS_AUTHORIZED'] === 'Y') {
        \Bitrix\Main\Application::getInstance()->getContext()->getResponse()->setStatus('403 Forbidden');
    } else {
        \Bitrix\Main\Application::getInstance()->getContext()->getResponse()->setStatus('401 Unauthorized');
    }
    return;
}

if ($arResult['CHEQUE_ITEMS']) {
    foreach ($arResult['CHEQUE_ITEMS'] as $item) {
        ?><tr>
            <td class="product-art"><?=htmlspecialcharsbx($item['ARTICLE_NUMBER'])?></td>
            <td class="product-name"><?=htmlspecialcharsbx($item['ARTICLE_NAME'])?></td>
            <td class="product-quantity"><?=htmlspecialcharsbx($item['QUANTITY'])?></td>
            <td class="product-bonus"><?=($item['BONUS'] ? htmlspecialcharsbx($item['BONUS']) : 'Акция')?></td>
        </tr><?php
    }
} else {
    ?><tr>
        <td colspan="3">Нет данных</td>
    </tr><?php
}
