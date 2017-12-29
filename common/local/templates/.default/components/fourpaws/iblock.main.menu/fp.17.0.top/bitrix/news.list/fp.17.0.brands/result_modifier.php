<?if (!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true) {
    die();
}
/**
 * Бренды в меню (алфавитный указатель, сгруппированный список, популярные бренды)
 * result_modifier.php
 *
 * @updated: 28.12.2017
 */
if (!$arResult['ITEMS']) {
    return;
}

$arResult['POPULAR_ITEMS_ARRAY_KEYS'] = array();

$arResult['GROUPING'] = array();
$arResult['GROUPING']['#'] = array(
    'TITLE' => '#',
    //'ANCHOR' => 'idx_'.md5('#'),
    'ITEMS_ARRAY_KEYS' => array()
);

foreach ($arResult['ITEMS'] as $mKey => &$arItem) {
    $mImgField = false;
    if ($arItem['PREVIEW_PICTURE'] || $arItem['DETAIL_PICTURE']) {
        $mImgField = $arItem['PREVIEW_PICTURE'] ? $arItem['PREVIEW_PICTURE'] : $arItem['DETAIL_PICTURE'];
    }
    $arItem['PRINT_PICTURE'] = $mImgField && is_array($mImgField) ? $mImgField : array();
    if ($mImgField) {
        if (!empty($arParams['RESIZE_WIDTH']) && !empty($arParams['RESIZE_HEIGHT'])) {
            try {
                $bCrop = isset($arParams['RESIZE_TYPE']) && $arParams['RESIZE_TYPE'] == 'BX_RESIZE_IMAGE_EXACT';

                if (is_array($mImgField)) {
                    $obImg = new \FourPaws\BitrixOrm\Model\ResizeImageDecorator($mImgField);
                } else {
                    $obImg = \FourPaws\BitrixOrm\Model\ResizeImageDecorator::createFromPrimary($mImgField);
                }
                $obImg->setResizeWidth(!$bCrop ? $arParams['RESIZE_WIDTH'] : max(array($arParams['RESIZE_HEIGHT'], $arParams['RESIZE_WIDTH'])));
                $obImg->setResizeHeight(!$bCrop ? $arParams['RESIZE_HEIGHT'] : max(array($arParams['RESIZE_HEIGHT'], $arParams['RESIZE_WIDTH'])));

                if ($bCrop) {
                    if (is_array($mImgField)) {
                        $obImg = new \FourPaws\BitrixOrm\Model\CropImageDecorator($mImgField);
                    } else {
                        $obImg = \FourPaws\BitrixOrm\Model\CropImageDecorator::createFromPrimary($mImgField);
                    }
                    $obImg->setCropWidth($arParams['RESIZE_WIDTH']);
                    $obImg->setCropHeight($arParams['RESIZE_HEIGHT']);
                }

                $arItem['PRINT_PICTURE'] = array(
                    'SRC' => $obImg->getSrc(),
                );
            } catch (\Exception $obException) {
            }
        }
    }

    $sFirstLetter = ToUpper(substr(trim($arItem['NAME']), 0, 1));
    $sFirstLetterReduced = $sFirstLetter;
    if (preg_match('#[^\p{L}]+#'.BX_UTF_PCRE_MODIFIER, $sFirstLetter)) {
        if (preg_match('#[0-9]+#'.BX_UTF_PCRE_MODIFIER, $sFirstLetter)) {
            $sFirstLetterReduced = '#';
        } else {
            $sFirstLetterReduced = '#';
        }
    }
    if (!isset($arResult['GROUPING'][$sFirstLetterReduced])) {
        $arResult['GROUPING'][$sFirstLetterReduced] = array(
            'TITLE' => $sFirstLetter,
            //'ANCHOR' => 'idx_'.md5($sFirstLetterReduced),
            'ITEMS_ARRAY_KEYS' => array()
        );
    }
    $arResult['GROUPING'][$sFirstLetterReduced]['ITEMS_ARRAY_KEYS'][] = $mKey;

    if ($arItem['PROPERTY_POPULAR_VALUE']) {
        $arResult['POPULAR_ITEMS_ARRAY_KEYS'][] = $mKey;
    }
}
unset($arItem);

ksort($arResult['GROUPING']);
