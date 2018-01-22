<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Блок "Рассказать в соцсетях", выводимый на детальных страницах публикаций
 *
 * @updated: 01.01.2018
 */

?><div class="b-container">
    <div class="b-social-big">
        <p>Рассказать в соцсетях</p>
        <div class="ya-share2--wrapper">
            <div class="ya-share2"
                 data-lang="en"
                 data-services="facebook,odnoklassniki,vkontakte"
                 data-url="<?= /** @noinspection PhpUnhandledExceptionInspection */
                 new \FourPaws\Decorators\FullHrefDecorator(
                     \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getRequestUri()
                 ) ?>"
                 data-title="<?php $APPLICATION->ShowTitle(false) ?>"
                 data-description="<?php $APPLICATION->ShowViewContent('social-share-description') ?>"
                 data-image="<?php $APPLICATION->ShowViewContent('social-share-image') ?>"
            >
            </div>
        </div>
    </div>
</div><?php
