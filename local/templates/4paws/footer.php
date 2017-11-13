<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var \CMain $APPLICATION
 *
 */
?>
</main>
<footer class="b-footer">
    <div class="b-footer__communication">
        <div class="b-container">
            <div class="b-footer__inner">
                <div class="b-footer-communication">
                    <span class="b-footer-communication__item">
                        <a class="b-footer-communication__link" href="tel:84732027626" title="+7 473 202-76-26">
                            +7 473 202-76-26
                        </a>
                        <span class="b-footer-communication__description">(доступен до 21:00)</span>
                    </span>
                    <span class="b-footer-communication__item">
                        <a class="b-footer-communication__link" href="tel:88007700022" title="+7 800 770-00-22">
                            +7 800 770-00-22
                        </a>
                        <span class="b-footer-communication__description">(круглосуточно)</span>
                    </span>
                    <span class="b-footer-communication__link-block">
                        <span class="b-footer-communication__item">
                            <a class="b-footer-communication__link" href="javascript:void(0);" title="Перезвоните мне">
                                <span class="b-icon b-icon--footer">
                                    <svg class="b-icon__svg" viewBox="0 0 10 16 " width="10px" height="16px">
                                        <use class="b-icon__use"
                                             xlink:href="/static/build/icons.svg#icon-phone-white"></use>
                                    </svg>
                                </span>
                                Перезвоните мне
                            </a>
                        </span>
                        <span class="b-footer-communication__item">
                            <a class="b-footer-communication__link" href="javascript:void(0);" title="Обратная связь">
                                <span class="b-icon b-icon--footer">
                                    <svg class="b-icon__svg" viewBox="0 0 16 11 " width="16px" height="11px">
                                        <use class="b-icon__use"
                                             xlink:href="/static/build/icons.svg#icon-feedback"></use>
                                    </svg>
                                </span>
                                Обратная связь
                            </a>
                        </span>
                        <span class="b-footer-communication__item">
                            <a class="b-footer-communication__link"
                               href="javascript:void(0);" title="Чат с консультантом">
                                <span class="b-icon b-icon--footer">
                                    <svg class="b-icon__svg" viewBox="0 0 16 16 " width="16px" height="16px">
                                        <use class="b-icon__use"
                                             xlink:href="/static/build/icons.svg#icon-chat-white"></use>
                                    </svg>
                                </span>
                                Чат с консультантом
                            </a>
                        </span>
                    </span>
                </div>
                <div class="b-social">
                    <ul class="b-social__list">
                        <li class="b-social__item">
                            <a class="b-social__link" href="javascript:void(0);" title="Facebook">
                                <span class="b-icon b-icon--fb">
                                    <svg class="b-icon__svg" viewBox="0 0 9 18 " width="9px" height="18px">
                                        <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-fb"></use>
                                    </svg>
                                </span>
                            </a>
                        </li>
                        <li class="b-social__item">
                            <a class="b-social__link" href="javascript:void(0);" title="Odnoklassniki">
                                <span class="b-icon b-icon--ok">
                                    <svg class="b-icon__svg" viewBox="0 0 11 18 " width="11px" height="18px">
                                        <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-ok"></use>
                                    </svg>
                                </span>
                            </a>
                        </li>
                        <li class="b-social__item">
                            <a class="b-social__link" href="javascript:void(0);" title="VK">
                                <span class="b-icon b-icon--vk">
                                    <svg class="b-icon__svg" viewBox="0 0 22 13 " width="22px" height="13px">
                                        <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-vk"></use>
                                    </svg>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
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
                    <?php
                    /**
                     * @todo Подписка. Заменить компонентом и удалить файл.
                     */
                    require_once 'temp_subscription.php';
                    ?>
                </div>
                <?php require_once 'blocks/application_links.php'; ?>
            </div>
            <div class="b-footer__line">
                <div class="b-footer__column">
                    <div class="b-copyright">
                        <div class="b-copyright__copyright">
                            &copy; <?= (new DateTime())->format('Y') ?> Зоомагазин «Четыре лапы»
                        </div>
                        <a class="b-copyright__link"
                           href="/company/user-agreement/" title="Пользовательское соглашение">
                            Пользовательское соглашение
                        </a>
                        <a class="b-copyright__link b-copyright__link--personal" href="/company/privacy-policy/"
                           title="Условия использования персональных данных">
                            Условия использования персональных данных
                        </a>
                    </div>
                </div>
                <div class="b-footer__column b-footer__column--small">
                    <a class="b-adv" href="https://adv.ru/" title="Сделано в ADV" target="_blank">
                        <span class="b-icon b-icon--adv">
                            <svg class="b-icon__svg" viewBox="0 0 24 24 " width="24px" height="24px">
                                <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-logo-adv"></use>
                            </svg>
                        </span>
                        Сделано в ADV
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>
</div>
<?php /** @todo Markup */ ?>
<script src="/static/build/js/external.js"></script>
<script src="/static/build/js/internal.js"></script>

</body>
</html>
