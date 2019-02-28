<?php

/**
 * @var $sViewportCookie - Значение куки отвечающе за переключение вьпорта с мобильного на десктоп.
 */
$isViewportCookie = $_COOKIE['viewport'] ?? null;
$isBottomDieBanner = $_COOKIE['bottom_die_banner'] ?? null;

?>

<?php if (($isViewportCookie !== null) && ($isBottomDieBanner === null)) { ?>
    <div class="b-bottom-die-banner" data-bottom-die-banner="true">
        <div class="b-bottom-die-banner__container">
            <div class="b-bottom-die-banner__img-wrap">
                <img src="" class="b-bottom-die-banner__img b-bottom-die-banner__img--desktop">
                <img src="" class="b-bottom-die-banner__img b-bottom-die-banner__img--tablet">
                <img src="" class="b-bottom-die-banner__img b-bottom-die-banner__img--mobile">
            </div>
            <div class="b-bottom-die-banner__title"></div>
            <div class="b-bottom-die-banner__close" data-btn-close-bottom-die-banner="true"></div>
        </div>
    </div>
<?php } ?>