<?php

/**
 * @var $sViewportCookie - Значение куки отвечающе за переключение вьпорта с мобильного на десктоп.
 */
$isViewportCookie = $_COOKIE['viewport'] ?? null;

?>

<div class="b-promo-bottom-acarid js-promo-bottom-acarid hide <?/*php if ($isViewportCookie === null) { ?>mobile-hide<?php } */?>">
    <div class="b-promo-bottom-acarid__img"></div>
    <div class="b-promo-bottom-acarid__content">
        <div class="b-promo-bottom-acarid__title"><span>Защити питомца</span><br/> от блох и клещей</div>
        <a href="/catalog/veterinarnaya-apteka/zashchita-ot-blokh-i-kleshchey/" class="b-promo-bottom-acarid__btn">Защитить</a>
        <div class="b-promo-bottom-acarid__close js-close-promo-bottom-acarid"></div>
    </div>
</div>