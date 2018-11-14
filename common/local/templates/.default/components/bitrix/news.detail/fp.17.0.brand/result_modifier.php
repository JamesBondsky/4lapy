<?if (!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true) {
    die();
}

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/**
 * Карточка бренда (в разделе брендов)
 *
 * @updated: 25.12.2017
 */


$mImgField = false;
if ($arResult['DETAIL_PICTURE']) {
    $mImgField = $arResult['DETAIL_PICTURE'];
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

function getCropImage(array $arImage, int $width, int $height): CropImageDecorator
{
    $cropImage = new CropImageDecorator($arImage);

    $cropImage->setCropWidth($width)->setCropHeight($height);
    return $cropImage;
}

if (!empty($arResult['DISPLAY_PROPERTIES']['BLOCKS_SHOW_SWITCHER']['VALUE'])) {
    $arResult['SHOW_BLOCKS'] = json_decode(
        htmlspecialcharsBack(
            $arResult['DISPLAY_PROPERTIES']['BLOCKS_SHOW_SWITCHER']['VALUE']
        ),
        true
    );
} else {
    $arResult['SHOW_BLOCKS'] = [
        'BANNER_IMAGES_DESKTOP' => false,
        'VIDEO_MP4' => false,
        'PRODUCT_CATEGORIES' => false
    ];
}

$uploadDir = COption::GetOptionString("main", "upload_dir", "upload");

if ($arResult['SHOW_BLOCKS']['BANNER_IMAGES_DESKTOP']) {
    if ($arResult['DISPLAY_PROPERTIES']['BANNER_IMAGES_DESKTOP']['VALUE'] == null ||
        $arResult['DISPLAY_PROPERTIES']['BANNER_IMAGES_NOTEBOOK']['VALUE'] == null ||
        $arResult['DISPLAY_PROPERTIES']['BANNER_IMAGES_MOBILE']['VALUE'] == null) {
        $arResult['SHOW_BLOCKS']['BANNER_IMAGES_DESKTOP'] = false;
    } else {
        $files = [
            $arResult['DISPLAY_PROPERTIES']['BANNER_IMAGES_DESKTOP']['VALUE'] => 'BANNER_IMAGES_DESKTOP',
            $arResult['DISPLAY_PROPERTIES']['BANNER_IMAGES_NOTEBOOK']['VALUE'] => 'BANNER_IMAGES_NOTEBOOK',
            $arResult['DISPLAY_PROPERTIES']['BANNER_IMAGES_MOBILE']['VALUE'] => 'BANNER_IMAGES_MOBILE'
        ];

        $dbFiles = CFile::GetList([], ['@ID' => implode(',', array_keys($files))]);
        while ($file = $dbFiles->Fetch()) {
            $path = '/' . $uploadDir . '/' . $file['SUBDIR'] . '/' . $file['FILE_NAME'];
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                $arResult['SHOW_BLOCKS']['BANNER_IMAGES_DESKTOP'] = false;
                break;
            } else {
                $arImage['src'] = $path;
                switch ($files[$file['ID']]) {
                    case 'BANNER_IMAGES_DESKTOP':
                        $width = 1280;
                        $height = 300;
                        break;
                    case 'BANNER_IMAGES_NOTEBOOK':
                        $width = 940;
                        $height = 250;
                        break;
                    case 'BANNER_IMAGES_MOBILE':
                        $width = 767;
                        $height = 160;
                        break;
                }
                $arResult['BANNER']['IMAGES'][$files[$file['ID']]] = getCropImage($arImage, $width, $height);

            }
        }
        $arResult['BANNER']['LINK'] = $arResult['DISPLAY_PROPERTIES']['BANNER_LINK']['VALUE'];
    }
}

if ($arResult['SHOW_BLOCKS']['VIDEO_MP4']) {
    if ($arResult['DISPLAY_PROPERTIES']['VIDEO_MP4']['VALUE'] == null ||
        $arResult['DISPLAY_PROPERTIES']['VIDEO_WEBM']['VALUE'] == null ||
        $arResult['DISPLAY_PROPERTIES']['VIDEO_OGG']['VALUE'] == null) {
        $arResult['SHOW_BLOCKS']['VIDEO_MP4'] = false;
    } else {
        $files = [
            $arResult['DISPLAY_PROPERTIES']['VIDEO_MP4']['VALUE'] => 'VIDEO_MP4',
            $arResult['DISPLAY_PROPERTIES']['VIDEO_WEBM']['VALUE'] => 'VIDEO_WEBM',
            $arResult['DISPLAY_PROPERTIES']['VIDEO_OGG']['VALUE'] => 'VIDEO_OGG'
        ];

        $dbFiles = CFile::GetList([], ['@ID' => implode(',', array_keys($files))]);
        while ($file = $dbFiles->Fetch()) {
            $path = '/' . $uploadDir . '/' . $file['SUBDIR'] . '/' . $file['FILE_NAME'];
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                $arResult['SHOW_BLOCKS']['VIDEO_MP4'] = false;
                break;
            } else {
                $arResult['VIDEO']['VIDEOS'][$files[$file['ID']]] = $path;
            }
        }
        $arResult['VIDEO']['TITLE'] = $arResult['DISPLAY_PROPERTIES']['VIDEO_TITLE']['VALUE'];
        $arResult['VIDEO']['DESCRIPTION'] = htmlspecialcharsBack($arResult['DISPLAY_PROPERTIES']['VIDEO_DESCRIPTION']['VALUE']['TEXT']);

        $arImage['src'] = CFile::GetPath($arResult['DISPLAY_PROPERTIES']['VIDEO_PREVIEW_PICTURE']['VALUE']);
        $arResult['VIDEO']['PREVIEW_PICTURE'] = getCropImage($arImage, 1011, 568);

    }
}

if ($arResult['SHOW_BLOCKS']['PRODUCT_CATEGORIES']) {
    $arResult['PRODUCT_CATEGORIES'] = json_decode(htmlspecialcharsBack($arResult['DISPLAY_PROPERTIES']['PRODUCT_CATEGORIES']['VALUE']),
        true);
    $files = [];
    foreach ($arResult['PRODUCT_CATEGORIES'] as $key => &$productCategory) {
        if (isset($productCategory['image_id'])) {
            $files[$productCategory['image_id']] = $key;
            unset($arResult['PRODUCT_CATEGORIES'][$key]['image_id']);
        }
    }
    $dbFiles = CFile::GetList([], ['@ID' => implode(',', array_keys($files))]);
    while ($file = $dbFiles->Fetch()) {
        $arImage['src'] = '/' . $uploadDir . '/' . $file['SUBDIR'] . '/' . $file['FILE_NAME'];
        $arResult['PRODUCT_CATEGORIES'][$files[$file['ID']]]['image'] = getCropImage($arImage, 273, 230);
        $arResult['PRODUCT_CATEGORIES'][$files[$file['ID']]]['alt'] = $file['DESCRIPTION'];
    }
}