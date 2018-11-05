<?if (!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true) {
    die();
}

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/**
 * Карточка бренда (в разделе брендов)
 *
 * @updated: 25.12.2017
 */


$mImgField = false;
if ($arResult['PREVIEW_PICTURE'] || $arResult['DETAIL_PICTURE']) {
    $mImgField = $arResult['PREVIEW_PICTURE'] ? $arResult['PREVIEW_PICTURE'] : $arResult['DETAIL_PICTURE'];
}
$arResult['PRINT_PICTURE'] = $mImgField && is_array($mImgField) ? $mImgField : array();
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

            $arResult['PRINT_PICTURE'] = array(
                'SRC' => $obImg->getSrc(),
            );
        } catch (\Exception $obException) {
        }
    }
}
// в кэше это поле нужно только если будет использоваться component_epilog.php
$this->__component->SetResultCacheKeys(
    array(
        'PRINT_PICTURE',
    )
);

if (!empty($arResult['PROPERTIES']['BLOCKS_SHOW_SWITCHER']['~VALUE'])) {
    $arResult['SHOW_BLOCKS'] = json_decode($arResult['PROPERTIES']['BLOCKS_SHOW_SWITCHER']['~VALUE'], true);
} else {
    $arResult['SHOW_BLOCKS'] = [
        'SLIDER_IMAGES' => false,
        'VIDEO' => false,
        'SECTIONS' => false
    ];
}

if ($arResult['SHOW_BLOCKS']['SLIDER_IMAGES']) {
    //TODO WTF CFile::GetList don`t return SRC?
    foreach ($arResult['PROPERTIES']['SLIDER_IMAGES']['VALUE'] as $fileID) {
        $arResult['SLIDER_IMAGE'][] = CFile::GetPath($fileID);
    }
}

if ($arResult['SHOW_BLOCKS']['VIDEO']) {
    $arResult['VIDEO'][] = [
        'picture' => CFile::GetPath($arResult['PROPERTIES']['VIDEO']['VALUE']),
        'description' => $arResult['PROPERTIES']['VIDEO_DESCRIPTION']['VALUE']
    ];
}

if ($arResult['SHOW_BLOCKS']['SECTIONS']) {
    $arFilter = ['IBLOCK_TYPE' => IblockType::CATALOG, 'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS), 'ID' => $arResult['PROPERTIES']['SECTIONS']['VALUE']];
    $dbSections = CIBlockSection::GetList(null, $arFilter);
    while ($section = $dbSections->GetNext()) {
        $arResult['SECTIONS'][$section['ID']] = [
            'title' => $section['NAME'],
            'link' => $section['SECTION_PAGE_URL']
        ];
        if ($section['PICTURE']) {
            $arResult['SECTIONS'][$section['ID']]['picture'] = CFile::GetPath($section['PICTURE']);
        } elseif ($section['DETAIL_PICTURE']) {
            $arResult['SECTIONS'][$section['ID']]['picture'] = CFile::GetPath($section['DETAIL_PICTURE']);
        }
    }
}