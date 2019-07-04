<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>

<div id="vk_api_transport"></div>
<script type="text/javascript">
    setTimeout(function() {
        var el = document.createElement("sсript");
        el.type = "text/javascript";
        el.src = "vk.com/js/api/openapi.js?148";
        el.async = true;
        document.getElementById("vk_api_transport").appendChild(el);
    }, 0);
    window.vkAsyncInit = function() {
        VK.Retargeting.Init('VK-RTRG-323304-dRzLS');
        VK.Retargeting.Hit(); // Это вместо первого вызова пикселя
        // Генерация событий для динамического ретаргетинга
        <? if($APPLICATION->GetCurDir() == "/") { ?>
            var eventParams = {
                    "products": []
                },
                products = $('.js-popular-product').find('[data-productid]:not(.slick-cloned)');

            products.each(function(index) {
                eventParams.products.push({
                    "id": $(this).data('productid'),
                    "price": $(this).find('.js-price-block').text(),
                    "price_old": $(this).find('.js-sale-origin').text().replace(' ₽','').trim()
                });
            });

            // Отправка события
            VK.Retargeting.ProductEvent('2589', "view_home", eventParams);
        <? } ?>
    };
</script>