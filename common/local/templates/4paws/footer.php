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
    <?php }
    /** Основной прелоадер из gui */ ?>
    <?php include __DIR__ . '/blocks/preloader.php'; ?>
<?php } ?>
</main>
<footer class="b-footer <?= $template->getFooterClass() ?>">
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
                                'COMPONENT_TEMPLATE' => 'footer.menu',
                                'ROOT_MENU_TYPE' => 'top',
                                'MENU_CACHE_TYPE' => 'A',
                                'MENU_CACHE_TIME' => '360000',
                                'CACHE_SELECTED_ITEMS' => 'N',
                                'TEMPLATE_NO_CACHE' => 'N',
                                'MENU_CACHE_GET_VARS' => [],
                                'MAX_LEVEL' => '2',
                                'CHILD_MENU_TYPE' => 'left',
                                'USE_EXT' => 'N',
                                'DELAY' => 'N',
                                'ALLOW_MULTI_SELECT' => 'N',
                            ],
                            false
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
                <div class="b-footer__column b-footer__column--small">
                    <?php require_once __DIR__ . '/blocks/footer/creator.php' ?>
                </div>
            </div>
        </div>
    </div>
</footer>
<div class="b-shadow b-shadow--popover js-open-shadow"></div>
</div>
<?php require_once __DIR__ . '/blocks/footer/popups.php' ?>
<script src="<?= $markup->getJsFile() ?>"></script>
</body>
</html>
