<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CMain $APPLICATION
 */

use FourPaws\App\Application as PawsApplication;

$markup = PawsApplication::markup();
?>
</main>
<footer class="b-footer">
    <div class="b-footer__communication">
        <div class="b-container">
            <div class="b-footer__inner">
                <div class="b-footer-communication">
                    <?php require_once 'blocks/footer/communication_area.php' ?>
                </div>
                <?php require_once 'blocks/footer/social_links.php' ?>
            </div>
        </div>
    </div>
    <div class="b-footer__nav">
        <div class="b-container">
            <div class="b-footer__line">
                <div class="b-footer__column js-here-permutantion">
                    <?php $APPLICATION->IncludeComponent('bitrix:menu',
                                                         'footer.menu',
                                                         [
                                                             'COMPONENT_TEMPLATE'    => 'footer.menu',
                                                             'ROOT_MENU_TYPE'        => 'top',
                                                             'MENU_CACHE_TYPE'       => 'A',
                                                             'MENU_CACHE_TIME'       => '360000',
                                                             'MENU_CACHE_USE_GROUPS' => 'N',
                                                             'MENU_CACHE_GET_VARS'   => [],
                                                             'MAX_LEVEL'             => '2',
                                                             'CHILD_MENU_TYPE'       => 'left',
                                                             'USE_EXT'               => 'N',
                                                             'DELAY'                 => 'N',
                                                             'ALLOW_MULTI_SELECT'    => 'N',
                                                         ],
                                                         false); ?>
                    <?php $APPLICATION->IncludeComponent('fourpaws:expertsender.form',
                                                         '',
                                                         [],
                                                         false,
                                                         ['HIDE_ICONS' => 'Y']); ?>
                </div>
                <?php require_once 'blocks/footer/application_links.php'; ?>
            </div>
            <div class="b-footer__line">
                <div class="b-footer__column">
                    <?php require_once 'blocks/footer/copyright.php' ?>
                </div>
                <div class="b-footer__column b-footer__column--small">
                    <?php require_once 'blocks/footer/creator.php' ?>
                </div>
            </div>
        </div>
    </div>
</footer>
<div class="b-shadow b-shadow--popover js-open-shadow"></div>
</div>
<?php require_once 'blocks/footer/popups.php' ?>
<script src="<?= $markup->getJsFile() ?>"></script>
</body>
</html>
