<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
} ?>

<div id="vk_api_transport">
</div>

<script type="text/javascript" data-skip-moving=true>
    function vkRetargeting() {
        VK.Retargeting.Init('VK-RTRG-323304-dRzLS');
        VK.Retargeting.Hit(); // Это вместо первого вызова пикселя

        // Генерация событий для динамического ретаргетинга

        var eventParams = { products: [] };

        if (location.pathname === '/') {
            var products = $('.js-popular-product')
                .find('[data-productid]:not(.slick-cloned)');

            products.each(function(index) {
                eventParams.products.push({
                    "id": $(this).data('productid'),
                    "price": $(this).find('.js-price-block').text(),
                    "price_old": $(this).find('.js-sale-origin').text().replace(' ₽','').trim()
                });
            });

            console.log('<vk retargeting>', 'view_home', eventParams);
            VK.Retargeting.ProductEvent('2589', 'view_home', eventParams);
        } else if (
            location.pathname === '/catalog/search/'
        ) {
            $('.js-product-item').each(function() {
                eventParams.products.push({
                    id: $(this).data('productid'),
                    price: $(this).find('.js-price-block').text(),
                    price_old: $(this).find('.js-sale-origin').text().replace(' ₽','').trim()
                });
            });

            eventParams.search_string = (function() {
                var query = window.location.search.substring(1);
                var vars = query.split('&');

                for (var i = 0; i < vars.length; i++) {
                    var pair = vars[i].split('=');

                    if (decodeURIComponent(pair[0]) == "query") {
                        return decodeURIComponent(pair[1]);
                    }
                }
            })();

            console.log('<vk retargeting>', 'view_search', eventParams);
            VK.Retargeting.ProductEvent('2589', 'view_search', eventParams);
        } else if (
            location.pathname.startsWith('/catalog/')
            && !location.pathname.endsWith('.html')
        ) {
            // страницы каталога
            $('.js-product-item').each(function() {
                eventParams.products.push({
                    id: $(this).data('productid'),
                    price: $(this).find('.js-price-block').text(),
                    price_old: $(this).find('.js-sale-origin').text().replace(' ₽','').trim()
                });
            });

            console.log('<vk retargeting>', 'view_catalog', eventParams);
            VK.Retargeting.ProductEvent('2589', 'view_catalog', eventParams);
        } else if (
            location.pathname.startsWith('/catalog/')
            && location.pathname.endsWith('.html')
        ) {
            // страница продукта
            var $productContainer = $('.b-product-card'),
                $mainProduct = $productContainer.find('.b-product-card__product');

            eventParams.products.push({
                id: $productContainer.data('productid'),
                price: $mainProduct.find('.js-current-offer-price').text().trim(),
                price_old: $mainProduct.find('.js-current-offer-price-old').text().trim(),
            });

            console.log('<vk retargeting>', 'view_product', eventParams);
            VK.Retargeting.ProductEvent('2589', 'view_product', eventParams);
        } else if (location.pathname === "/cart/") {
            $('.b-button--start-order').click(function() {
                eventParams.products = [];

                $('.js-item-shopping').each(function() {
                    eventParams.products.push({
                        id: $(this).data('productid'),
                        price: $(this).find('.b-price .b-price__current').text().trim(),
                        price_old: $(this).find('.b-price .b-old-price__old').text().trim(),
                    });
                });

                console.log('<vk retargeting>', 'init_checkout', eventParams);
                VK.Retargeting.ProductEvent('2589', 'init_checkout', eventParams);
            });
        } else if (location.pathname === "/sale/order/payment/") {
            $('.js-order-step-3-submit').click(function() {
                eventParams.products = [];

                var products = $(this).data('products');

                Object.keys(products).forEach(function(key) {
                    eventParams.products.push({
                        id: key,
                        price: products[key]
                    });
                });

                console.log('<vk retargeting>', 'add_payment_info', eventParams);
                VK.Retargeting.ProductEvent('2589', 'add_payment_info', eventParams);
            });
        } else if (location.pathname.startsWith('/sale/order/complete/')) {
            console.log('<vk retargeting>', 'purchase', {});
            VK.Retargeting.ProductEvent('2589', 'purchase', {});
        }

        $('.js-basket-add').on('click', function() {
            eventParams.products = [{
                id: $(this).data('offerid'),
                price: $(this).find('.js-price-block').text().trim()
            }];

            console.log('<vk retargeting>', 'add_to_cart', eventParams);
            VK.Retargeting.ProductEvent('2589', 'add_to_cart', eventParams);
        });

        $('.js-cart-delete-item').on('click', function() {
            eventParams.products = [{
                id: $(this).data('basketid'),
                price: $(this).parent().find('.b-price__current').text().trim()
            }];

            console.log('<vk retargeting>', 'remove_from_cart', eventParams);
            VK.Retargeting.ProductEvent('2589', 'remove_from_cart', eventParams);
        });
    }

    window.vkAsyncInitCallbacks = [vkRetargeting];

    setTimeout(function() {
        var el = document.createElement("sсript");
        el.type = "text/javascript";
        el.src = "vk.com/js/api/openapi.js?148";
        el.async = true;
        document.getElementById("vk_api_transport").appendChild(el);
    }, 0);
</script>