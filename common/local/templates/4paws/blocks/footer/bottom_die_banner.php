<?php

/**
 * @var $sViewportCookie - Значение куки отвечающе за переключение вьпорта с мобильного на десктоп.
 */
$isViewportCookie = $_COOKIE['viewport'] ?? null;
$isBottomDieBanner = $_COOKIE['bottom_die_banner'] ?? null;

?>

<div class="b-bottom-die-banner js-bottom-die-banner <?php if ($isViewportCookie === null) { ?>mobile-hide<?php } ?> <?php if ($isBottomDieBanner !== null) { ?>hide<?php } ?>">
    <div class="b-bottom-die-banner__container">
        <img src="/static/build/images/inhtml/animals-bottom-die-banner.png" class="b-bottom-die-banner__img b-bottom-die-banner__img--animals">
        <div class="b-bottom-die-banner__content">
            <div class="b-bottom-die-banner__title"><span>Защити питомца</span> от блох и клещей</div>
            <a href="/catalog/veterinarnaya-apteka/zashchita-ot-blokh-i-kleshchey/ " class="b-bottom-die-banner__btn">Защитить</a>
            <div class="b-bottom-die-banner__close js-btn-close-bottom-die-banner"></div>
        </div>
    </div>
</div>