<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CMain $APPLICATION
 */

use Bitrix\Main\Application;
use FourPaws\App\Application as PawsApplication;
use FourPaws\App\MainTemplate;
use FourPaws\Decorators\SvgDecorator;

/**
 * @var $sViewportCookie - Значение куки отвечающе за переключение вьпорта с мобильного на десктоп.
 */
$sViewportCookie = $_COOKIE['viewport'] ?? null;

$markup = PawsApplication::markup();
/** @var MainTemplate $template */
if (!isset($template) || !($template instanceof MainTemplate)) {
    $template = MainTemplate::getInstance(Application::getInstance()->getContext());
}

if ($template->hasMainWrapper()) {
    if ($template->hasHeaderPublicationListContainer()) {
        ?>
        </div>
        </div>
    <?php }

    if ($template->hasHeaderBlockShopList()) { ?>
        </div>
        </div>
    <?php }

    if ($template->hasHeaderPersonalContainer()) { ?>
        </main>
        </div>
        <?php include __DIR__ . '/blocks/preloader.php'; ?>
        </div>
    <?php } ?>
    <div class="article_popup__wrapper--hidden js-article-popup">
        <div class="article_popup__bg js-article-popup-bg"></div>
        <div class="article_popup">
            <div class="article_popup_nav">
                <div class="article_popup_nav__cross js-article-close">
                    <?= new SvgDecorator('cross') ?>
                </div>
            </div>
            <div class="article_popup__title js-popup-title"></div>
            <div class="article_popup__image js-popup-image"></div>
            <div class="article_popup__text_container js-popup-text"></div>
        </div>
    </div>
    <?php /** Основной прелоадер из gui */ ?>
    <?php include __DIR__ . '/blocks/preloader.php'; ?>
    </main>
<?php } ?>
<?php require_once __DIR__ . '/blocks/footer/change_viewport.php'; ?>
<footer class="b-footer js-main-footer <?= $template->getFooterClass() ?>">
    <?php if (!$template->hasShortHeaderFooter()) { ?>
        <div class="b-footer__communication">
            <div class="b-container">
                <div class="b-footer__inner">
                    <div class="b-footer-communication">
                        <?php require_once __DIR__ . '/blocks/footer/communication_area.php' ?>
                    </div>
                    <?php require_once __DIR__ . '/blocks/footer/social_links.php' ?>
                </div>
            </div>
        </div>
    <?php } ?>
    <div class="b-footer__nav">
        <div class="b-container">
            <?php if (!$template->hasShortHeaderFooter()) { ?>
                <div class="b-footer__line">
                    <div class="b-footer__column js-here-permutantion">
                        <?php $APPLICATION->IncludeComponent(
                            'bitrix:menu',
                            'footer.menu',
                            [
                                'COMPONENT_TEMPLATE'   => 'footer.menu',
                                'ROOT_MENU_TYPE'       => 'top',
                                'MENU_CACHE_TYPE'      => 'A',
                                'MENU_CACHE_TIME'      => '360000',
                                'CACHE_SELECTED_ITEMS' => 'N',
                                'TEMPLATE_NO_CACHE'    => 'N',
                                'MENU_CACHE_GET_VARS'  => [],
                                'MAX_LEVEL'            => '2',
                                'CHILD_MENU_TYPE'      => 'left',
                                'USE_EXT'              => 'N',
                                'DELAY'                => 'N',
                                'ALLOW_MULTI_SELECT'   => 'N',
                            ],
                            false,
                            ['HIDE_ICONS' => 'Y']
                        ); ?>
                        <?php $APPLICATION->IncludeComponent(
                            'fourpaws:expertsender.form',
                            '',
                            [],
                            false,
                            ['HIDE_ICONS' => 'Y']
                        ); ?>
                    </div>
                    <?php require_once __DIR__ . '/blocks/footer/application_links.php'; ?>
                </div>
            <?php } ?>
            <div class="b-footer__line">
                <div class="b-footer__column">
                    <?php require_once __DIR__ . '/blocks/footer/copyright.php' ?>
                </div>
                <div class="b-footer__column 
                            b-footer__column--small 
                            b-footer__column--change-viewport 
                            <?= ($sViewportCookie === 'mobile')||($sViewportCookie === 'desktop') ? 'active' : '' ?>"
                            data-footer-links-change-viewport="true">

                    <div class="link-toggle-view <?= $sViewportCookie === 'desktop' ? 'active' : '' ?>" data-change-viewport-mode='desktop' data-type="mobile">
                        Перейти в мобильную версию
                    </div>
                    <div class="link-toggle-view <?= $sViewportCookie === 'mobile' ? 'active' : '' ?>" data-change-viewport-mode='mobile' data-type="desktop">
                        Перейти в полноэкранный режим
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<div class="b-shadow js-shadow"></div>
<div class="b-shadow b-shadow--popover js-open-shadow"></div>
</div>
<?php require_once __DIR__ . '/blocks/footer/popups.php' ?>
<script src="<?= $markup->getJsFile() ?>"></script>
<script>
    <?php /** хз насколько кросбраузерно */?>
    window.onbeforeunload = function () {
        $.get('/ajax/sale/forgot-basket/close-page/');
    }
</script>

<script src="/static/build/js/external/snowfall.js" type="text/javascript"></script>
<script type="text/javascript">
    $(function(){
        if(BX.GetWindowScrollSize().scrollWidth >= 768 && !BX.browser.IsIOS() && !BX.browser.IsAndroid()){
            $(document).snowfall({image:"/static/build/images/inhtml/snow1.png", collection:'.b-header__info', collectionHeight:10, minSize:10, maxSize:30, flakeCount:50, maxSpeed:2});
        }
    });
</script>

</body>
</html>
