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
                    <div class="col-sm-12">
                        <h5>ФЕСТИВАЛЬ «ЧЕТЫРЕ ЛАПЫ» 2019</h5>
                    </div>
                </div>
                <!--// end row -->
            </div>
        </div>
        <!-- End Links -->
    </footer>
    <!--========== END FOOTER ==========-->
</div>

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
<?/*<script src="vendor/jquery.smooth-scroll.js" type="text/javascript"></script>*/?>
<script src="vendor/jquery.wow.min.js" type="text/javascript"></script>
<script src="vendor/swiper/js/swiper.jquery.min.js" type="text/javascript"></script>
<script src="vendor/magnific-popup/jquery.magnific-popup.min.js" type="text/javascript"></script>
<script src="vendor/masonry/jquery.masonry.pkgd.min.js" type="text/javascript"></script>
<script src="vendor/masonry/imagesloaded.pkgd.min.js" type="text/javascript"></script>
<!-- Скрипт для блокирования скролла, кроме выбранного блока. Нужен для скрола попапов на айфоне -->
<script src="vendor/dom-locky/dom-locky.js" type="text/javascript"></script>

<!-- PAGE LEVEL SCRIPTS -->
<?/*<script src="js/shapes.js" type="text/javascript"></script>*/?>
<script src="js/jquery.scrollme.min.js" type="text/javascript"></script>
<script src="js/layout.min.js" type="text/javascript"></script>
<script src="js/components/wow.min.js" type="text/javascript"></script>
<script src="js/components/swiper.min.js" type="text/javascript"></script>
<script src="js/components/maginific-popup.min.js" type="text/javascript"></script>
<script src="js/components/masonry.min.js" type="text/javascript"></script>
<script src="js/components/gmap.min.js" type="text/javascript"></script>
<script src="js/components/forms.js" type="text/javascript"></script>

<script>
    var tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    function checkPosition(){
        //функция проверки видимости элемента на jquery
        var div_position = $('#videoHolder').offset();
        var div_top = div_position.top;
        var div_left = div_position.left;
        var div_width = $('#videoHolder').width();
        var div_height = $('#videoHolder').height();
        var top_scroll = $(document).scrollTop();
        var left_scroll = $(document).scrollLeft();
        var screen_width = $(window).width();
        var screen_height = $(window).height()+600;
        var see_x1 = left_scroll;
        var see_x2 = screen_width + left_scroll;
        var see_y1 = top_scroll;
        var see_y2 = screen_height + top_scroll;
        var div_x1 = div_left;
        var div_x2 = div_left + div_height;
        var div_y1 = div_top;
        var div_y2 = div_top + div_width;
        if( div_x1 >= see_x1 && div_x2 <= see_x2 && div_y1 >= see_y1 && div_y2 <= see_y2 ){
            //если элемент видим на экране, запускаем видео Youtube
            player.playVideo();
        }else{
            //если не видим, ставим видео на паузу
            player.pauseVideo();
        }
    }

    $(document).ready(function(){
        //запускаем функцию проверки видимости элемента
        $(document).scroll(function(){
            checkPosition();
        });
        $(window).resize(function(){
            checkPosition();
        });
    });

    function onYouTubeIframeAPIReady() {
        //рисуем видеопроигрыватель Youtube
        player = new YT.Player('videoHolder', {

            playerVars: { 'autoplay': 0, 'controls': 0, 'showinfo': 0, 'rel': 0}, //тонкие настройки видеопроигрывателя
            videoId: 'yqijA5mbcoU', //здесь id ролика
        });}
</script>

</body>
</html>
