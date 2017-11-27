<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @noinspection PhpUndefinedClassInspection */
/** @global CMain $APPLICATION */
/** @noinspection PhpUndefinedClassInspection */
/** @global CUser $USER */
/** @noinspection PhpUndefinedClassInspection */
/** @global CDatabase $DB */
/** @noinspection PhpUndefinedClassInspection */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @noinspection PhpUndefinedClassInspection */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<?php $ElementID = $APPLICATION->IncludeComponent('bitrix:news.detail',
                                                  '',
                                                  [
                                                      'DISPLAY_DATE'              => $arParams['DISPLAY_DATE'],
                                                      'DISPLAY_NAME'              => $arParams['DISPLAY_NAME'],
                                                      'DISPLAY_PICTURE'           => $arParams['DISPLAY_PICTURE'],
                                                      'DISPLAY_PREVIEW_TEXT'      => $arParams['DISPLAY_PREVIEW_TEXT'],
                                                      'IBLOCK_TYPE'               => $arParams['IBLOCK_TYPE'],
                                                      'IBLOCK_ID'                 => $arParams['IBLOCK_ID'],
                                                      'FIELD_CODE'                => $arParams['DETAIL_FIELD_CODE'],
                                                      'PROPERTY_CODE'             => $arParams['DETAIL_PROPERTY_CODE'],
                                                      'DETAIL_URL'                => $arResult['FOLDER']
                                                                                     . $arResult['URL_TEMPLATES']['detail'],
                                                      'SECTION_URL'               => $arResult['FOLDER']
                                                                                     . $arResult['URL_TEMPLATES']['section'],
                                                      'META_KEYWORDS'             => $arParams['META_KEYWORDS'],
                                                      'META_DESCRIPTION'          => $arParams['META_DESCRIPTION'],
                                                      'BROWSER_TITLE'             => $arParams['BROWSER_TITLE'],
                                                      'SET_CANONICAL_URL'         => $arParams['DETAIL_SET_CANONICAL_URL'],
                                                      'DISPLAY_PANEL'             => $arParams['DISPLAY_PANEL'],
                                                      'SET_LAST_MODIFIED'         => $arParams['SET_LAST_MODIFIED'],
                                                      'SET_TITLE'                 => $arParams['SET_TITLE'],
                                                      'MESSAGE_404'               => $arParams['MESSAGE_404'],
                                                      'SET_STATUS_404'            => $arParams['SET_STATUS_404'],
                                                      'SHOW_404'                  => $arParams['SHOW_404'],
                                                      'FILE_404'                  => $arParams['FILE_404'],
                                                      'INCLUDE_IBLOCK_INTO_CHAIN' => $arParams['INCLUDE_IBLOCK_INTO_CHAIN'],
                                                      'ADD_SECTIONS_CHAIN'        => $arParams['ADD_SECTIONS_CHAIN'],
                                                      'ACTIVE_DATE_FORMAT'        => $arParams['DETAIL_ACTIVE_DATE_FORMAT'],
                                                      'CACHE_TYPE'                => $arParams['CACHE_TYPE'],
                                                      'CACHE_TIME'                => $arParams['CACHE_TIME'],
                                                      'CACHE_GROUPS'              => $arParams['CACHE_GROUPS'],
                                                      'USE_PERMISSIONS'           => $arParams['USE_PERMISSIONS'],
                                                      'GROUP_PERMISSIONS'         => $arParams['GROUP_PERMISSIONS'],
                                                      'DISPLAY_TOP_PAGER'         => $arParams['DETAIL_DISPLAY_TOP_PAGER'],
                                                      'DISPLAY_BOTTOM_PAGER'      => $arParams['DETAIL_DISPLAY_BOTTOM_PAGER'],
                                                      'PAGER_TITLE'               => $arParams['DETAIL_PAGER_TITLE'],
                                                      'PAGER_SHOW_ALWAYS'         => 'N',
                                                      'PAGER_TEMPLATE'            => $arParams['DETAIL_PAGER_TEMPLATE'],
                                                      'PAGER_SHOW_ALL'            => $arParams['DETAIL_PAGER_SHOW_ALL'],
                                                      'CHECK_DATES'               => $arParams['CHECK_DATES'],
                                                      'ELEMENT_ID'                => $arResult['VARIABLES']['ELEMENT_ID'],
                                                      'ELEMENT_CODE'              => $arResult['VARIABLES']['ELEMENT_CODE'],
                                                      'SECTION_ID'                => $arResult['VARIABLES']['SECTION_ID'],
                                                      'SECTION_CODE'              => $arResult['VARIABLES']['SECTION_CODE'],
                                                      'IBLOCK_URL'                => $arResult['FOLDER']
                                                                                     . $arResult['URL_TEMPLATES']['news'],
                                                      'USE_SHARE'                 => $arParams['USE_SHARE'],
                                                      'SHARE_HIDE'                => $arParams['SHARE_HIDE'],
                                                      'SHARE_TEMPLATE'            => $arParams['SHARE_TEMPLATE'],
                                                      'SHARE_HANDLERS'            => $arParams['SHARE_HANDLERS'],
                                                      'SHARE_SHORTEN_URL_LOGIN'   => $arParams['SHARE_SHORTEN_URL_LOGIN'],
                                                      'SHARE_SHORTEN_URL_KEY'     => $arParams['SHARE_SHORTEN_URL_KEY'],
                                                      'ADD_ELEMENT_CHAIN'         => $arParams['ADD_ELEMENT_CHAIN'] ??
                                                                                     '',
                                                      'STRICT_SECTION_CHECK'      => $arParams['STRICT_SECTION_CHECK']
                                                                                     ?? '',
                                                  ],
                                                  $component); ?>
<?php /**
 * TODO сделать распродажу с каталогом
 */
//if ($ElementID && $arParams['USE_CATEGORIES'] === 'Y'):
//    global $arCategoryFilter;
//    /** @noinspection PhpUndefinedClassInspection */
//    $obCache = new CPHPCache;
//    $strCacheID = $componentPath . LANG . $arParams['IBLOCK_ID'] . $ElementID . $arParams['CATEGORY_CODE'];
//    /** @noinspection PhpUndefinedClassInspection */
//    if (($tzOffset = CTimeZone::GetOffset()) !== 0) {
//        $strCacheID .= '_' . $tzOffset;
//    }
//    /** @noinspection PhpUndefinedClassInspection */
//    if ($arParams['CACHE_TYPE'] === 'N'
//        || ($arParams['CACHE_TYPE'] === 'A'
//            && \COption::GetOptionString('main',
//                                         'component_cache_on',
//                                         'Y') === 'N')) {
//        $CACHE_TIME = 0;
//    } else {
//        $CACHE_TIME = $arParams['CACHE_TIME'];
//    }
//    if ($obCache->StartDataCache($CACHE_TIME, $strCacheID, $componentPath)) {
//        /** @noinspection PhpUndefinedClassInspection */
//        $rsProperties = CIBlockElement::GetProperty($arParams['IBLOCK_ID'],
//                                                    $ElementID,
//                                                    'sort',
//                                                    'asc',
//                                                    [
//                                                            'ACTIVE' => 'Y',
//                                                            'CODE'   => $arParams['CATEGORY_CODE'],
//                                                        ]);
//        $arCategoryFilter = [];
//        while ($arProperty = $rsProperties->Fetch()) {
//            if (is_array($arProperty['VALUE']) && count($arProperty['VALUE']) > 0) {
//                foreach ($arProperty['VALUE'] as $value) {
//                    $arCategoryFilter[$value] = true;
//                }
//            } elseif (!is_array($arProperty['VALUE']) && !empty($arProperty['VALUE'])) {
//                /** @noinspection PhpIllegalArrayKeyTypeInspection */
//                $arCategoryFilter[$arProperty['VALUE']] = true;
//            }
//        }
//        $obCache->EndDataCache($arCategoryFilter);
//    } else {
//        $arCategoryFilter = $obCache->GetVars();
//    }
//    if (count($arCategoryFilter) > 0):
//        $arCategoryFilter = [
//            'PROPERTY_' . $arParams['CATEGORY_CODE'] => array_keys($arCategoryFilter),
//            '!' . 'ID'                               => $ElementID,
//        ];
//        ?>
<!--        <hr /><h3>--><? //= GetMessage('CATEGORIES') ?><!--</h3>-->
<!--        --><?php //if (is_array($arParams['CATEGORY_IBLOCK']) && !empty($arParams['CATEGORY_IBLOCK'])) {
//        foreach ($arParams['CATEGORY_IBLOCK'] as $iblock_id):?>
<!--            --><?php //$APPLICATION->IncludeComponent('bitrix:news.list',
//                                                 $arParams['CATEGORY_THEME_' . $iblock_id],
//                                                 [
//                                                     'IBLOCK_ID'                 => $iblock_id,
//                                                     'NEWS_COUNT'                => $arParams['CATEGORY_ITEMS_COUNT'],
//                                                     'SET_TITLE'                 => 'N',
//                                                     'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
//                                                     'CACHE_TYPE'                => $arParams['CACHE_TYPE'],
//                                                     'CACHE_TIME'                => $arParams['CACHE_TIME'],
//                                                     'CACHE_GROUPS'              => $arParams['CACHE_GROUPS'],
//                                                     'FILTER_NAME'               => 'arCategoryFilter',
//                                                     'CACHE_FILTER'              => 'Y',
//                                                     'DISPLAY_TOP_PAGER'         => 'N',
//                                                     'DISPLAY_BOTTOM_PAGER'      => 'N',
//                                                 ],
//                                                 $component); ?>
<!--        --><? // endforeach;
//    } ?>
<!--    --><? // endif; ?>
<? // endif; ?>
<div class="b-container">
    <section class="b-common-section">
        <div class="b-common-section__title-box b-common-section__title-box--sale">
            <h2 class="b-title b-title--sale">Распродажа</h2><a class="b-link b-link--title"
                                                                href="javascript:void(0)"
                                                                title="Показать все"><span class="b-link__text b-link__text--title">Показать все</span><span
                        class="b-link__mobile b-link__mobile--title">Все</span><span class="b-icon"><svg class="b-icon__svg"
                                                                                                         viewBox="0 0 6 10 "
                                                                                                         width="6px"
                                                                                                         height="10px"><use
                                class="b-icon__use"
                                xlink:href="icons.svg#icon-arrow-right"></use></svg></span></a>
        </div>
        <div class="b-common-section__content b-common-section__content--sale js-popular-product">
            <div class="b-common-item js-product-item"><span class="b-common-item__sticker-wrap"
                                                             style="background-color:;data-background:;"><img
                            class="b-common-item__sticker"
                            src="images/inhtml/s-15proc.svg"
                            alt=""
                            role="presentation" /></span><span class="b-common-item__image-wrap"><img class="b-common-item__image js-weight-img"
                                                                                                      src="images/content/royal-canin-2.jpg"
                                                                                                      alt="Роял Канин"
                                                                                                      title="" /></span>
                <div
                        class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                    href="javascript:void(0);"
                                                                    title=""><span class="b-clipped-text b-clipped-text--three"><span><strong>Роял Канин</strong> корм для собак крупных пород макси эдалт</span></span></a>
                    <div
                            class="b-weight-container b-weight-container--list">
                        <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                           href="javascript:void(0);"
                           title=""></a>
                        <ul class="b-weight-container__list">
                            <li class="b-weight-container__item">
                                <a class="b-weight-container__link js-price active-link"
                                   href="javascript:void(0);"
                                   data-price="100"
                                   data-image="images/content/royal-canin-2.jpg">4 кг</a>
                            </li>
                            <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                    href="javascript:void(0);"
                                                                    data-price="4 199"
                                                                    data-image="images/content/abba.png">15
                                                                                                         кг</a>
                            </li>
                        </ul>
                    </div>
                    <a class="b-common-item__add-to-cart"
                       href="javascript:void(0);"
                       title=""><span class="b-common-item__wrapper-link"><span class="b-cart"><span class="b-icon b-icon--cart"><svg
                                            class="b-icon__svg"
                                            viewBox="0 0 16 16 "
                                            width="16px"
                                            height="16px"><use class="b-icon__use"
                                                               xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                    class="b-common-item__price js-price-block">100</span><span class="b-common-item__currency">₽</span></span></a>
                    <div
                            class="b-common-item__additional-information">
                        <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 ₽</span><span
                                    class="b-common-item__discount"><span class="b-common-item__disc">Скидка</span><span
                                        class="b-common-item__discount-price">200</span><span class="b-common-item__currency">₽</span></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-common-item js-product-item"><span class="b-common-item__sticker-wrap"
                                                             style="background-color:;data-background:;"><img
                            class="b-common-item__sticker"
                            src="images/inhtml/s-gift.svg"
                            alt=""
                            role="presentation" /></span><span class="b-common-item__image-wrap"><img class="b-common-item__image js-weight-img"
                                                                                                      src="images/content/hills-cat.jpg"
                                                                                                      alt="Хиллс"
                                                                                                      title="" /></span>
                <div
                        class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                    href="javascript:void(0);"
                                                                    title=""><span class="b-clipped-text b-clipped-text--three"><span><strong>Хиллс</strong> корм для кошек тунец стерилайз</span></span></a>
                    <div class="b-weight-container b-weight-container--list">
                        <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                           href="javascript:void(0);"
                           title=""></a>
                        <ul class="b-weight-container__list">
                            <li class="b-weight-container__item">
                                <a class="b-weight-container__link js-price active-link"
                                   href="javascript:void(0);"
                                   data-price="2 585"
                                   data-image="images/content/hills-cat.jpg">3,5 кг</a>
                            </li>
                            <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                    href="javascript:void(0);"
                                                                    data-price="2 585"
                                                                    data-image="images/content/brit.png">8
                                                                                                         кг</a>
                            </li>
                        </ul>
                    </div>
                    <a class="b-common-item__add-to-cart"
                       href="javascript:void(0);"
                       title=""><span class="b-common-item__wrapper-link"><span class="b-cart"><span class="b-icon b-icon--cart"><svg
                                            class="b-icon__svg"
                                            viewBox="0 0 16 16 "
                                            width="16px"
                                            height="16px"><use class="b-icon__use"
                                                               xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                    class="b-common-item__price js-price-block">2 585</span><span class="b-common-item__currency">₽</span></span></a>
                    <div
                            class="b-common-item__additional-information">
                        <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 ₽</span><span
                                    class="b-common-item__discount"><span class="b-common-item__disc">Скидка</span><span
                                        class="b-common-item__discount-price">200</span><span class="b-common-item__currency">₽</span></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-common-item js-product-item"><span class="b-common-item__sticker-wrap"
                                                             style="background-color:;data-background:;"><img
                            class="b-common-item__sticker"
                            src="images/inhtml/s-proc.svg"
                            alt=""
                            role="presentation" /></span><span class="b-common-item__image-wrap"><img class="b-common-item__image js-weight-img"
                                                                                                      src="images/content/clean-cat.jpg"
                                                                                                      alt="CleanCat"
                                                                                                      title="" /></span>
                <div
                        class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                    href="javascript:void(0);"
                                                                    title=""><span class="b-clipped-text b-clipped-text--three"><span><strong>CleanCat</strong> наполнитель для кошачьего туалета силикагель</span></span></a>
                    <div class="b-weight-container b-weight-container--list">
                        <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                           href="javascript:void(0);"
                           title=""></a>
                        <ul class="b-weight-container__list">
                            <li class="b-weight-container__item">
                                <a class="b-weight-container__link js-price active-link"
                                   href="javascript:void(0);"
                                   data-price="353"
                                   data-image="images/content/clean-cat.jpg">5 л</a>
                            </li>
                            <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                    href="javascript:void(0);"
                                                                    data-price="915"
                                                                    data-image="images/content/pro-plan.jpg">10
                                                                                                             л</a>
                            </li>
                        </ul>
                    </div>
                    <a class="b-common-item__add-to-cart"
                       href="javascript:void(0);"
                       title=""><span class="b-common-item__wrapper-link"><span class="b-cart"><span class="b-icon b-icon--cart"><svg
                                            class="b-icon__svg"
                                            viewBox="0 0 16 16 "
                                            width="16px"
                                            height="16px"><use class="b-icon__use"
                                                               xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                    class="b-common-item__price js-price-block">353</span><span class="b-common-item__currency">₽</span></span></a>
                    <div
                            class="b-common-item__additional-information">
                        <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 ₽</span><span
                                    class="b-common-item__discount"><span class="b-common-item__disc">Скидка</span><span
                                        class="b-common-item__discount-price">200</span><span class="b-common-item__currency">₽</span></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-common-item js-product-item"><span class="b-common-item__sticker-wrap"
                                                             style="background-color:;data-background:;"><img
                            class="b-common-item__sticker"
                            src="images/inhtml/s-30proc.svg"
                            alt=""
                            role="presentation" /></span><span class="b-common-item__image-wrap"><img class="b-common-item__image js-weight-img"
                                                                                                      src="images/content/pro-plan.jpg"
                                                                                                      alt="ПроПлан"
                                                                                                      title="" /></span>
                <div
                        class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                    href="javascript:void(0);"
                                                                    title=""><span class="b-clipped-text b-clipped-text--three"><span><strong>ПроПлан</strong> корм для кастрированных / стерилизованны...</span></span></a>
                    <div class="b-weight-container b-weight-container--list">
                        <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                           href="javascript:void(0);"
                           title=""></a>
                        <ul class="b-weight-container__list">
                            <li class="b-weight-container__item">
                                <a class="b-weight-container__link js-price active-link"
                                   href="javascript:void(0);"
                                   data-price="167"
                                   data-image="images/content/pro-plan.jpg">400 г</a>
                            </li>
                            <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                    href="javascript:void(0);"
                                                                    data-price="167"
                                                                    data-image="images/content/bozita.png">1,5
                                                                                                           кг</a>
                            </li>
                            <li class="b-weight-container__item">
                                <a class="b-weight-container__link js-price unavailable-link"
                                   href="javascript:void(0);"
                                   data-price="167"
                                   data-image="images/content/bozita.png">3 кг</a>
                            </li>
                            <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                    href="javascript:void(0);"
                                                                    data-price="5 199"
                                                                    data-image="images/content/hills-dog.jpg">10
                                                                                                              кг</a>
                            </li>
                        </ul>
                    </div>
                    <a class="b-common-item__add-to-cart"
                       href="javascript:void(0);"
                       title=""><span class="b-common-item__wrapper-link"><span class="b-cart"><span class="b-icon b-icon--cart"><svg
                                            class="b-icon__svg"
                                            viewBox="0 0 16 16 "
                                            width="16px"
                                            height="16px"><use class="b-icon__use"
                                                               xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                    class="b-common-item__price js-price-block">167</span><span class="b-common-item__currency">₽</span></span></a>
                    <div
                            class="b-common-item__additional-information">
                        <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 ₽</span><span
                                    class="b-common-item__discount"><span class="b-common-item__disc">Скидка</span><span
                                        class="b-common-item__discount-price">200</span><span class="b-common-item__currency">₽</span></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-common-item js-product-item"><span class="b-common-item__sticker-wrap"
                                                             style="background-color:;data-background:;"><img
                            class="b-common-item__sticker"
                            src="images/inhtml/s-20proc.svg"
                            alt=""
                            role="presentation" /></span><span class="b-common-item__image-wrap"><img class="b-common-item__image js-weight-img"
                                                                                                      src="images/content/hills-dog.jpg"
                                                                                                      alt="Хиллс"
                                                                                                      title="" /></span>
                <div
                        class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                    href="javascript:void(0);"
                                                                    title=""><span class="b-clipped-text b-clipped-text--three"><span><strong>Хиллс</strong> корм для собак крупных пород с курицей</span></span></a>
                    <div class="b-weight-container b-weight-container--list">
                        <ul class="b-weight-container__list b-weight-container__list--one">
                            <li class="b-weight-container__item">
                                <a class="b-weight-container__link js-price active-link"
                                   href="javascript:void(0);"
                                   data-price="3 749"
                                   data-image="images/content/hills-dog.jpg">12 кг</a>
                            </li>
                        </ul>
                    </div>
                    <a class="b-common-item__add-to-cart"
                       href="javascript:void(0);"
                       title=""><span class="b-common-item__wrapper-link"><span class="b-cart"><span class="b-icon b-icon--cart"><svg
                                            class="b-icon__svg"
                                            viewBox="0 0 16 16 "
                                            width="16px"
                                            height="16px"><use class="b-icon__use"
                                                               xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                    class="b-common-item__price js-price-block">3 749</span><span class="b-common-item__currency">₽</span></span></a>
                    <div
                            class="b-common-item__additional-information">
                        <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 ₽</span><span
                                    class="b-common-item__discount"><span class="b-common-item__disc">Скидка</span><span
                                        class="b-common-item__discount-price">200</span><span class="b-common-item__currency">₽</span></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-common-item js-product-item"><span class="b-common-item__sticker-wrap"
                                                             style="background-color:;data-background:;"></span><span
                        class="b-common-item__image-wrap"><img class="b-common-item__image js-weight-img"
                                                               src="images/content/bozita.png"
                                                               alt="ПроПлан"
                                                               title="" /></span>
                <div
                        class="b-common-item__info-center-block"><a class="b-common-item__description-wrap"
                                                                    href="javascript:void(0);"
                                                                    title=""><span class="b-clipped-text b-clipped-text--three"><span><strong>ПроПлан</strong> корм для кастрированных / стерилизованны...</span></span></a>
                    <div class="b-weight-container b-weight-container--list">
                        <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select"
                           href="javascript:void(0);"
                           title=""></a>
                        <ul class="b-weight-container__list">
                            <li class="b-weight-container__item">
                                <a class="b-weight-container__link js-price active-link"
                                   href="javascript:void(0);"
                                   data-price="167"
                                   data-image="images/content/bozita.png">400 г</a>
                            </li>
                            <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                    href="javascript:void(0);"
                                                                    data-price="167"
                                                                    data-image="images/content/bozita.png">1,5
                                                                                                           кг</a>
                            </li>
                            <li class="b-weight-container__item">
                                <a class="b-weight-container__link js-price unavailable-link"
                                   href="javascript:void(0);"
                                   data-price="167"
                                   data-image="images/content/bozita.png">3 кг</a>
                            </li>
                            <li class="b-weight-container__item"><a class="b-weight-container__link js-price"
                                                                    href="javascript:void(0);"
                                                                    data-price="5 199"
                                                                    data-image="images/content/bozita.png">10
                                                                                                           кг</a>
                            </li>
                        </ul>
                    </div>
                    <a class="b-common-item__add-to-cart"
                       href="javascript:void(0);"
                       title=""><span class="b-common-item__wrapper-link"><span class="b-cart"><span class="b-icon b-icon--cart"><svg
                                            class="b-icon__svg"
                                            viewBox="0 0 16 16 "
                                            width="16px"
                                            height="16px"><use class="b-icon__use"
                                                               xlink:href="icons.svg#icon-cart"></use></svg></span></span><span
                                    class="b-common-item__price js-price-block">167</span><span class="b-common-item__currency">₽</span></span></a>
                    <div
                            class="b-common-item__additional-information">
                        <div class="b-common-item__benefin"><span class="b-common-item__prev-price">100 ₽</span><span
                                    class="b-common-item__discount"><span class="b-common-item__disc">Скидка</span><span
                                        class="b-common-item__discount-price">200</span><span class="b-common-item__currency">₽</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="b-container">
    <? /** TODO сделать Рассказать в соцсетях  */
    /*if (array_key_exists('USE_SHARE', $arParams) && $arParams['USE_SHARE'] === 'Y') {
    ?>
    <div class="news-detail-share">
        <noindex>
            <?php
            $APPLICATION->IncludeComponent('bitrix:main.share',
                                           '',
                                           [
                                               'HANDLERS'          => $arParams['SHARE_HANDLERS'],
                                               'PAGE_URL'          => $arResult['~DETAIL_PAGE_URL'],
                                               'PAGE_TITLE'        => $arResult['~NAME'],
                                               'SHORTEN_URL_LOGIN' => $arParams['SHARE_SHORTEN_URL_LOGIN'],
                                               'SHORTEN_URL_KEY'   => $arParams['SHARE_SHORTEN_URL_KEY'],
                                               'HIDE'              => $arParams['SHARE_HIDE'],
                                           ],
                                           $component,
                                           ['HIDE_ICONS' => 'Y']);
            ?>
        </noindex>
    </div>
    <?php
    }*/ ?>
    <div class="b-social-big">
        <p>Рассказать в соцсетях</p>
        <ul>
            <li><a href="#"><span class="b-icon b-icon--fb"><svg class="b-icon__svg"
                                                                 viewBox="0 0 12 22 "
                                                                 width="12px"
                                                                 height="22px"><use class="b-icon__use"
                                                                                    xlink:href="icons.svg#icon-fb"></use></svg></span></a>
            </li>
            <li><a href="#"><span class="b-icon b-icon--ok"><svg class="b-icon__svg"
                                                                 viewBox="0 0 14 23 "
                                                                 width="14px"
                                                                 height="23px"><use class="b-icon__use"
                                                                                    xlink:href="icons.svg#icon-ok"></use></svg></span></a>
            </li>
            <li><a href="#"><span class="b-icon b-icon--vk"><svg class="b-icon__svg"
                                                                 viewBox="0 0 29 17 "
                                                                 width="29px"
                                                                 height="17px"><use class="b-icon__use"
                                                                                    xlink:href="icons.svg#icon-vk"></use></svg></span></a>
            </li>
        </ul>
    </div>
</div>
<?php /** TODO сделать добавление комментариев */
if ($ElementID && $arParams['USE_REVIEW'] === 'Y' && IsModuleInstalled('forum')): ?>
    <div class="b-container">
        <div class="b-comment-block">
            <p class="b-comment-block__title">Комментарии</p>
            <p class="b-comment-block__info">Пока никто не оставил комментарии.</p>
            <p class="b-comment-block__auth"><a href="#">Авторизуйтесь</a> , чтобы написать комментарий.</p>
        </div>
    </div>
    <?php $APPLICATION->IncludeComponent('bitrix:forum.topic.reviews',
                                         '',
                                         [
                                             'CACHE_TYPE'           => $arParams['CACHE_TYPE'],
                                             'CACHE_TIME'           => $arParams['CACHE_TIME'],
                                             'MESSAGES_PER_PAGE'    => $arParams['MESSAGES_PER_PAGE'],
                                             'USE_CAPTCHA'          => $arParams['USE_CAPTCHA'],
                                             'PATH_TO_SMILE'        => $arParams['PATH_TO_SMILE'],
                                             'FORUM_ID'             => $arParams['FORUM_ID'],
                                             'URL_TEMPLATES_READ'   => $arParams['URL_TEMPLATES_READ'],
                                             'SHOW_LINK_TO_FORUM'   => $arParams['SHOW_LINK_TO_FORUM'],
                                             'DATE_TIME_FORMAT'     => $arParams['DETAIL_ACTIVE_DATE_FORMAT'],
                                             'ELEMENT_ID'           => $ElementID,
                                             'AJAX_POST'            => $arParams['REVIEW_AJAX_POST'],
                                             'IBLOCK_ID'            => $arParams['IBLOCK_ID'],
                                             'URL_TEMPLATES_DETAIL' => $arResult['FOLDER']
                                                                       . $arResult['URL_TEMPLATES']['detail'],
                                         ],
                                         $component); ?>
<? endif ?>
