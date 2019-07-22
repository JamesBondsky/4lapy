<?

define('DOBROLAP_FOLDER', '/dobrolap/');

use Bitrix\Main\Page\Asset;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'https://fonts.googleapis.com/css?family=Poppins:100,200,300,400,500,600,700,800,900');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/open-iconic-bootstrap.min.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/animate.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/owl.carousel.min.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/owl.theme.default.min.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/magnific-popup.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/aos.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/ionicons.min.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/flaticon.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/icomoon.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/style.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/lightbox.min.css');

Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/jquery.min.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/jquery-migrate-3.0.1.min.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/popper.min.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/bootstrap.min.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/jquery.easing.1.3.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/jquery.waypoints.min.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/jquery.stellar.min.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/owl.carousel.min.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/jquery.magnific-popup.min.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/aos.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/jquery.animateNumber.min.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/scrollax.min.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/dom-locky.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/lightbox.min.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/lightbox-plus-jquery.min.js');
Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'js/main.js');