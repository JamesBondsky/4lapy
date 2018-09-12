<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application;
use FourPaws\EcommerceBundle\Service\RetailRocketService;

/**
 * @todo вынести всё в бандл
 */
$container = Application::getInstance()->getContainer();
$retailRocket = $container->get(RetailRocketService::class);
?>
<script data-skip-moving="true">
    <?= $retailRocket->renderTracking() ?>
    window.dataLayer = window.dataLayer || [];

    (function (w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({
            'gtm.start':
                new Date().getTime(), event: 'gtm.js'
        });
        var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
        j.async = true;
        j.src =
            'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-NXNPF4Z');
</script>
