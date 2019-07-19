<?

define('DOBROLAP_FOLDER', '/dobrolap/');

use Bitrix\Main\Page\Asset;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/bootstrap.min.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'style.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/colors.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/versions.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/responsive.css');
Asset::getInstance()->addCss(DOBROLAP_FOLDER . 'css/custom.css');

//Asset::getInstance()->addJs(DOBROLAP_FOLDER . 'some.js');
