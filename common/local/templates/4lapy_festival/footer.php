<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CMain $APPLICATION
 */

use Bitrix\Main\Application;
use FourPaws\App\Application as PawsApplication;

$markup = PawsApplication::markup();
?>

<!--========== FOOTER ==========-->
<footer class="footer">
    <!-- Links -->
    <div class="section-seperator">
        <div class="content-md container">
            <div class="row">

            </div>
            <!--// end row -->
        </div>
    </div>
    <!-- End Links -->

    <!-- Copyright -->
    <div class="content container">
        <div class="row">
            <div class="col-xs-6">
                <img class="footer-logo" src="img/logo-dark.png" alt="Aironepage Logo">
            </div>
            <div class="col-xs-6 text-right">
                <p class="margin-b-0"><a class="fweight-700" href="http://keenthemes.com/preview/aironepage/">Aironepage</a> Theme Powered by: <a class="fweight-700" href="http://www.keenthemes.com/">KeenThemes.com</a></p>
            </div>
        </div>
        <!--// end row -->
    </div>
    <!-- End Copyright -->
</footer>
<!--========== END FOOTER ==========-->

<!-- Back To Top -->
<a href="javascript:void(0);" class="js-back-to-top back-to-top">Наверх</a>

<div class="b-shadow js-shadow"></div>
<div class="b-shadow b-shadow--popover js-open-shadow"></div>

<?php /** Основной прелоадер из gui */ ?>
<?php include __DIR__ . '/blocks/preloader.php'; ?>

<?php require_once __DIR__ . '/blocks/footer/popups.php' ?>
<script src="<?= $markup->getJsFile() ?>"></script>

<!-- JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- CORE PLUGINS -->
<script src="vendor/jquery.min.js" type="text/javascript"></script>
<script src="vendor/jquery-migrate.min.js" type="text/javascript"></script>
<script src="vendor/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

<!-- PAGE LEVEL PLUGINS -->
<script src="vendor/jquery.easing.js" type="text/javascript"></script>
<script src="vendor/jquery.back-to-top.js" type="text/javascript"></script>
<script src="vendor/jquery.smooth-scroll.js" type="text/javascript"></script>
<script src="vendor/jquery.wow.min.js" type="text/javascript"></script>
<script src="vendor/swiper/js/swiper.jquery.min.js" type="text/javascript"></script>
<script src="vendor/magnific-popup/jquery.magnific-popup.min.js" type="text/javascript"></script>
<script src="vendor/masonry/jquery.masonry.pkgd.min.js" type="text/javascript"></script>
<script src="vendor/masonry/imagesloaded.pkgd.min.js" type="text/javascript"></script>

<!-- PAGE LEVEL SCRIPTS -->
<?/*!--<script src="js/shapes.js" type="text/javascript"></script>--*/?>
<script src="js/jquery.scrollme.min.js" type="text/javascript"></script>
<script src="js/layout.min.js" type="text/javascript"></script>
<script src="js/components/wow.min.js" type="text/javascript"></script>
<script src="js/components/swiper.min.js" type="text/javascript"></script>
<script src="js/components/maginific-popup.min.js" type="text/javascript"></script>
<script src="js/components/masonry.min.js" type="text/javascript"></script>
<script src="js/components/gmap.min.js" type="text/javascript"></script>

<!-- MAIN SITE ADDITIONAL -->

</body>
</html>
