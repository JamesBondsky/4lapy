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
use FourPaws\KioskBundle\Service\KioskService;

$markup = PawsApplication::markup();
/** @var MainTemplate $template */
if (!isset($template) || !($template instanceof MainTemplate)) {
    $template = MainTemplate::getInstance(Application::getInstance()->getContext());
}

if ($template->hasMainWrapper()) { ?>

    <?php /** Основной прелоадер из gui */ ?>
    <?php include __DIR__ . '/blocks/preloader.php'; ?>

    </main>
<?php } ?>

</div>

<div class="bottom-landing">

    <section data-id-section-landing="where-buy" class="where-buy-leto2020">
        <div class="b-container">
            <div class="where-buy-leto2020__top">
                <div class="title-leto2020">Где купить?</div>
            </div>
        </div>
        <div class="where-buy-leto2020__map" id="mapWhereBuylanding" data-map-where-buy-landing="0"></div>
    </section>

    <section data-id-section-landing="winners" class="winners-leto2020">
        <?php $APPLICATION->IncludeComponent('articul:action.winners', 'leto2020', ['SECTION_CODE' => 'LETO2020']); ?>
    </section>

    <section data-id-section-landing="photo-winners" class="photo-winners-leto2020">
        <?php $APPLICATION->IncludeComponent('articul:action.winners', 'withphoto', ['SECTION_CODE' => 'LETO2020']); ?>
    </section>

    <section class="questions-leto2020" data-id-section-landing="questions">
        <?php $APPLICATION->IncludeComponent('articul:leto2020.questions', ''); ?>
    </section>
</div>
</div>

<footer class="b-footer js-main-footer <?= $template->getFooterClass() ?>">
    <?php if (!$template->hasShortHeaderFooter()) { ?>
        <?php if(!KioskService::isKioskMode()) { ?>
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
    <?php } ?>
    <div class="b-footer__nav">
        <div class="b-container">
            <?php if (!$template->hasShortHeaderFooter()) { ?>
                <div class="b-footer__line">
                    <?php if(!KioskService::isKioskMode()) { ?>
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
                    <?php } ?>
                    <?php require_once __DIR__ . '/blocks/footer/application_links.php'; ?>
                </div>
            <?php } ?>
            <div class="b-footer__line b-footer__line--change-viewport">
                <div class="b-footer__column">
                    <?php require_once __DIR__ . '/blocks/footer/copyright.php' ?>
                </div>
                <?php if(!KioskService::isKioskMode()) { ?>
                    <div class="b-footer__column
                                b-footer__column--small
                                b-footer__column--change-viewport
                                <?= ($sViewportCookie === 'mobile') ? 'mobile' : '' ?>"
                         data-footer-links-change-viewport="true">
                        <?php if ($sViewportCookie === null) { ?>
                            <div class="link-toggle-view active mobile" data-change-viewport-mode='mobile' data-type="desktop">
                                Перейти в<br/> полноэкранный режим
                            </div>
                        <?php }else{ ?>
                            <div class="link-toggle-view <?= $sViewportCookie === 'desktop' ? 'active' : '' ?>" data-change-viewport-mode='desktop' data-type="mobile">
                                Перейти в<br/> мобильную версию
                            </div>
                            <div class="link-toggle-view <?= $sViewportCookie === 'mobile' ? 'active mobile' : '' ?>" data-change-viewport-mode='mobile' data-type="desktop">
                                Перейти в<br/> полноэкранный режим
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</footer>


<div class="b-shadow js-shadow"></div>
<div class="b-shadow b-shadow--popover js-open-shadow"></div>
</div>
<?php require_once __DIR__ . '/blocks/footer/popups.php' ?>
<script src="<?= $markup->getJsFile() ?>"></script>
<script src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js"></script>
<script src="//yastatic.net/share2/share.js"></script>

</body>
</html>
