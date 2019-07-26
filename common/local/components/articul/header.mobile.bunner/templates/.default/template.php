<?/**
 * @var $sHideMobileBannerCookie - Значение куки отвечающе за отмену показа мобильного баннера
 */
$sHideMobileBannerCookie = $_COOKIE['hide_mobile_app'] ?? null;

if ($arResult['SHOW_BANNER'] && (int)$sHideMobileBannerCookie !== 1) { ?>
    <div class="b-mobile-app hidden js-banner-mobile-app">
        <div class="b-mobile-app__banner hidden js-banner-mobile-app-android">
            <a href="<?= $arResult['BANNER']['ANDROID_LINK'] ?>" class="b-mobile-app__link"
               style="background-image: url(<?= $arResult['BANNER']['ANDROID_IMAGE'] ?>)" target="_blank"
               data-img-banner-mobile-app="<?= $arResult['BANNER']['ANDROID_IMAGE'] ?>"></a>
        </div>
        <div class="b-mobile-app__banner hidden js-banner-mobile-app-ios">
            <a href="<?= $arResult['BANNER']['IOS_LINK'] ?>" class="b-mobile-app__link"
               style="background-image: url(<?= $arResult['BANNER']['IOS_IMAGE'] ?>)" target="_blank"
               data-img-banner-mobile-app="<?= $arResult['BANNER']['IOS_IMAGE'] ?>"></a>
        </div>
        <div class="b-mobile-app__close-wrap">
            <button type="button" class="b-mobile-app__close js-banner-mobile-app-close"></button>
        </div>
    </div>
<? } ?>
