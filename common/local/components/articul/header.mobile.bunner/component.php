<?

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$cacheTime = 7 * 24 * 60 * 60;

if ($this->startResultCache($cacheTime)) {

    $iblockId = IblockUtils::getIblockId('publications', 'mobile_app_banner');

    $ciDb = \CIBlockElement::GetList(
        [
            'ID' => 'DESC'
        ],
        [
            'IBLOCK_TYPE' => 'publications',
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y'
        ],
        false,
        [
            'nTopCount' => 1
        ],
        [
            'ID',
            'IBLOCK_ID',
            'GOOGLE_PLAY_LINK',
            'APP_STORE_LINK',
            'PREVIEW_PICTURE',
            'DETAIL_PICTURE'
        ]
    );

    $arResult['SHOW_BANNER'] = false;

    if ($ciEl = $ciDb->GetNextElement()) {
        $arResult['SHOW_BANNER'] = true;
        $arItem = $ciEl->GetFields();

        $arItem['PROPERTIES'] = $ciEl->GetProperties();
        if (
            $arItem['PREVIEW_PICTURE'] == null ||
            $arItem['DETAIL_PICTURE'] == null ||
            $arItem['PROPERTIES']['GOOGLE_PLAY_LINK']['VALUE'] == '' ||
            $arItem['PROPERTIES']['APP_STORE_LINK']['VALUE'] == ''
        ) {
            $arResult['SHOW_BANNER'] = false;
        } else {
            $arResult['BANNER'] = [
                'ANDROID_IMAGE' => \CFIle::GetPath($arItem['PREVIEW_PICTURE']),
                'IOS_IMAGE' => \CFIle::GetPath($arItem['DETAIL_PICTURE']),
                'ANDROID_LINK' => $arItem['PROPERTIES']['GOOGLE_PLAY_LINK']['VALUE'],
                'IOS_LINK' => $arItem['PROPERTIES']['APP_STORE_LINK']['VALUE']
            ];
        }
    }

    $this->setResultCacheKeys([
        'SHOW_BANNER',
        'ANDROID_IMAGE',
        'IOS_IMAGE',
        'ANDROID_LINK',
        'IOS_LINK'
    ]);

    $this->includeComponentTemplate();
}
