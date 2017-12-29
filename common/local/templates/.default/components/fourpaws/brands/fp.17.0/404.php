<?if (!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true) {
    die();
}
/**
 * Бренды: 404
 *
 * @updated: 25.12.2017
 */

$this->setFrameMode(false);

$arParams['SET_STATUS_404'] = isset($arParams['SET_STATUS_404']) ? $arParams['SET_STATUS_404'] : 'Y';
$arParams['SHOW_404'] = isset($arParams['SHOW_404']) ? $arParams['SHOW_404'] : 'Y';
$arParams['FILE_404'] = isset($arParams['FILE_404']) ? $arParams['FILE_404'] : '';
\Bitrix\Iblock\Component\Tools::process404(
    '',
    $arParams['SET_STATUS_404'] === 'Y',
    $arParams['SET_STATUS_404'] === 'Y',
    $arParams['SHOW_404'] === 'Y',
    $arParams['FILE_404']
);
