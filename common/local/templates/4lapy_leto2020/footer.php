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
        <div class="where-buy-leto2020__top">
            <div class="b-container">
                <div class="title-leto2020">Где купить?</div>
            </div>
        </div>
        <div class="where-buy-leto2020__map" id="mapWhereBuylanding" data-map-where-buy-landing="0"></div>
    </section>

    <section data-id-section-landing="winners" class="winners-leto2020">
        <div class="b-container">
            <div class="winners-leto2020__inner">
                <div class="title-leto2020">
                    Списки победителей
                </div>
                <div class="b-tab winners-leto2020__content">
                    <div class="b-tab-title">
                        <ul class="b-tab-title__list">
                            <li class="b-tab-title__item js-tab-item active">
                                <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-leto2020-0">
                                    <span class="b-tab-title__text">10.01</span>
                                </a>
                            </li>
                            <li class="b-tab-title__item js-tab-item">
                                <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-leto2020-1">
                                    <span class="b-tab-title__text">17.01</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="b-tab-content">
                        <div class="b-tab-content__container js-tab-content active" data-tab-content="winners-leto2020-0">
                            <div class="winners-leto2020__list">
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Басырова Ольга">
                                            <div class="item__icon item__icon_phone"></div>
                                            <span>Басырова Ольга</span>
                                        </div>
                                        <div class="item__phone">+7...9600</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Васильева Элонна">
                                            <div class="item__icon"></div>
                                            <span>Васильева Элонна</span>
                                        </div>
                                        <div class="item__phone">+7...0006</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Никитина Анастасия">
                                            <div class="item__icon"></div>
                                            <span>Никитина Анастасия</span>
                                        </div>
                                        <div class="item__phone">+7...3010</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Арзуманова Эвелина">
                                            <div class="item__icon"></div>
                                            <span>Арзуманова Эвелина</span>
                                        </div>
                                        <div class="item__phone">+7...9162</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Абросимов Денис">
                                            <div class="item__icon item__icon_phone"></div>
                                            <span>Абросимов Денис</span>
                                        </div>
                                        <div class="item__phone">+7...2775</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Осорин Андрей ">
                                            <div class="item__icon"></div>
                                            <span>Осорин Андрей</span>
                                        </div>
                                        <div class="item__phone">+7...1413</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Потапова Ирина ">
                                            <div class="item__icon"></div>
                                            <span>Потапова Ирина</span>
                                        </div>
                                        <div class="item__phone">+7...1397</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Лебедев Алексей ">
                                            <div class="item__icon item__icon_tickets"></div>
                                            <span>Лебедев Алексей</span>
                                        </div>
                                        <div class="item__phone">+7...4244</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Паршина Римма ">
                                            <div class="item__icon item__icon_tickets"></div>
                                            <span>Паршина Римма</span>
                                        </div>
                                        <div class="item__phone">+7...7899</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Самойлова Юлия ">
                                            <div class="item__icon"></div>
                                            <span>Самойлова Юлия</span>
                                        </div>
                                        <div class="item__phone">+7...1991</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="b-tab-content__container js-tab-content" data-tab-content="winners-leto2020-1">
                            <div class="winners-leto2020__list">
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Воеводина Александра">
                                            <div class="item__icon item__icon_tickets"></div>
                                            <span>Воеводина Александра</span>
                                        </div>
                                        <div class="item__phone">+7...1452</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Павловская Мария">
                                            <div class="item__icon"></div>
                                            <span>Павловская Мария</span>
                                        </div>
                                        <div class="item__phone">+7...7378</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Касаткина Наталья">
                                            <div class="item__icon item__icon_phone"></div>
                                            <span>Касаткина Наталья</span>
                                        </div>
                                        <div class="item__phone">+7...8304</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Калинина Наталия">
                                            <div class="item__icon"></div>
                                            <span>Калинина Наталия</span>
                                        </div>
                                        <div class="item__phone">+7...7484</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Сорокина София">
                                            <div class="item__icon item__icon_phone"></div>
                                            <span>Сорокина София</span>
                                        </div>
                                        <div class="item__phone">+7...3671</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Голосков Игорь">
                                            <div class="item__icon"></div>
                                            <span>Голосков Игорь</span>
                                        </div>
                                        <div class="item__phone">+7...2794</div>
                                    </div>
                                </div>
                                <div class="item__wrap">
                                    <div class="item">
                                        <div class="item__name" title="Соломатина Ирина">
                                            <div class="item__icon item__icon_tickets"></div>
                                            <span>Соломатина Ирина</span>
                                        </div>
                                        <div class="item__phone">+7...4176</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="questions-leto2020" data-id-section-landing="questions">
        <div class="b-container">
            <h2 class="title-leto2020 title-leto2020_blue">Вопросы и ответы</h2>
            <div class="questions-leto2020__accordion">
                <div class="item-accordion">
                    <div class="item-accordion__header js-toggle-accordion">
                        <span class="item-accordion__header-inner">КАК НАКОПИТЬ МАРКИ?</span>
                    </div>
                    <div class="item-accordion__block js-dropdown-block">
                        <div class="item-accordion__block-content">
                            <div class="item-accordion__block-text">
                                Марки начисляются с 1 октября по 30 ноября 2019 г.&nbsp; за покупки во всех розничных зоомагазинах, на сайте и в мобильном приложении.<br>
                                <b>За каждые 500р в чеке начисляется&nbsp;1 марка. </b><br>
                                Марки копятся в <a target="_blank" href="https://4lapy.ru/personal/marki/"><u><span style="color: #004a80;">личном кабинете</span></u></a> или на буклете, который выдается в зоомагазине.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-accordion">
                    <div class="item-accordion__header js-toggle-accordion">
                        <span class="item-accordion__header-inner">Я накопил марки. Как купить со скидкой 30%?</span>
                    </div>
                    <div class="item-accordion__block js-dropdown-block">
                        <div class="item-accordion__block-content">
                            <div class="item-accordion__block-text">
                                с 2 октября по 15 декабря 2019 г. накопленные марки можно обменять на скидки до -30% на любые товары из разделов лежаки, домики и когтеточки.<br>
                                <br>
                                <b>-30% = 12 марок</b><br>
                                <b>
                                    -20% = 9 марок</b><br>
                                <b>
                                    -10% = 6 марок</b><br>
                                <br>
                                &nbsp; Чтобы купить лежак, домик или когтеточку со скидкой необходимо:<br>
                                <ul>
                                    <li>на&nbsp;сайте или в&nbsp;приложении: добавь товар в&nbsp;корзину, нажми кнопку «списать марки» в корзине. скидка добавится автоматически.</li>
                                    <li>в&nbsp;магазине: предъяви буклет с наклеенными марками или сообщи кассиру номер телефона. кассир спишет марки и сделает скидку.</li>
                                </ul>
                                <br>
                                <b>Скидка за накопленные марки суммируется со всеми другими скидками и специальными предложениями.</b><br>
                                <b>15 декабря 2019г все неиспользованные марки сгорят.</b><br>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-accordion">
                    <div class="item-accordion__header js-toggle-accordion">
                        <span class="item-accordion__header-inner">Какие товары можно купить со скидкой 30%?</span>
                    </div>
                    <div class="item-accordion__block js-dropdown-block">
                        <div class="item-accordion__block-content">
                            <div class="item-accordion__block-text">
                                с 2 октября по 15 декабря 2019 г. накопленные марки можно обменять на скидки до -30% на любые товары из разделов:<br>
                                <ul>
                                    <li style="color: #005b7e;"><b><a target="_blank" href="https://4lapy.ru/catalog/sobaki/lezhaki-i-domiki/"><u>лежаки</u></a></b></li>
                                    <li style="color: #005b7e;"><b><a target="_blank" href="https://4lapy.ru/catalog/sobaki/lezhaki-i-domiki/domiki-myagkie-lezhaki/"><u>домики</u></a></b></li>
                                    <li style="color: #005b7e;"><b><u><a target="_blank" href="https://4lapy.ru/catalog/koshki/kogtetochki/">когтеточки</a></u></b></li>
                                </ul>
                                <br>
                                Скидка&nbsp; -30% = 12 марок<br>
                                Скидка&nbsp; -20% = 9 марок<br>
                                Скидка&nbsp; -10% = 6 марок<br>
                                <br>
                                <br>
                                <ul></ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-accordion">
                    <div class="item-accordion__header js-toggle-accordion">
                        <span class="item-accordion__header-inner">Суммируются ли скидки за марки с другими скидками?</span>
                    </div>
                    <div class="item-accordion__block js-dropdown-block">
                        <div class="item-accordion__block-content">
                            <div class="item-accordion__block-text">
                                <b>ДА.</b><br>
                                Скидка за накопленные марки суммируется со всеми другими скидками и специальными предложениями, которые в данный момент&nbsp; действуют на сайте.<br>
                                В первую очередь к товару применяется скидка по любой другой акции, затем начисляется скидка за накопленные марки.<br>
                                <br>
                                <br>
                                <br>
                                <ul></ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-accordion">
                    <div class="item-accordion__header js-toggle-accordion">
                        <span class="item-accordion__header-inner">Действуют ли марки в розничном магазине и на сайте?</span>
                    </div>
                    <div class="item-accordion__block js-dropdown-block">
                        <div class="item-accordion__block-content">
                            <div class="item-accordion__block-text">
                                <b>ДА.<br>
                                </b><br>
                                <ul>
                                    <li>Марки начисляются за любые покупки в розничных зоомагазинах, на сайте и в мобильном приложении.</li>
                                </ul>
                                <ul>
                                    <li>Скидку за накопленные марки можно получить в розничных магазинах, на сайте и в мобильном приложении.</li>
                                </ul>
                                <br>
                                <br>
                                &nbsp;Для получения скидки:<br>
                                <ul>
                                    <li>на&nbsp;сайте или в&nbsp;приложении: добавь товар в&nbsp;корзину, нажми кнопку «списать марки» в корзине. скидка добавится автоматически.</li>
                                    <li>в&nbsp;магазине: предъяви буклет с наклеенными марками или сообщи кассиру номер телефона. кассир спишет марки и сделает скидку</li>
                                </ul>
                                <b>
                                    <ul></ul>
                                </b>
                                <ul></ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-accordion">
                    <div class="item-accordion__header js-toggle-accordion">
                        <span class="item-accordion__header-inner">Суммируются ли бумажные и электронные марки?</span>
                    </div>
                    <div class="item-accordion__block js-dropdown-block">
                        <div class="item-accordion__block-content">
                            <div class="item-accordion__block-text">
                                <b>НЕТ.<br>
                                </b><br>
                                Марки, которые копятся в электронном виде в личном кабинете и марки-наклейки, которые копятся на буклете не суммируются друг с другом.<br>
                                Вы можете получить скидку или за электронные марки, или за&nbsp; марки-наклейки на буклете, но не одновременно.<b><br>
                                </b>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-accordion">
                    <div class="item-accordion__header js-toggle-accordion">
                        <span class="item-accordion__header-inner">Какой срок действия марок?</span>
                    </div>
                    <div class="item-accordion__block js-dropdown-block">
                        <div class="item-accordion__block-content">
                            <div class="item-accordion__block-text">
                                Срок накопления марок: с 1 октября по 30 ноября 2019 г.<b><br>
                                </b>Срок обмена марок на скидки: с 2 октября по 15 декабря (включительно) 2019 г.<br>
                                <br>
                                16 декабря все неиспользованные марки сгорят.<br>
                                <br>
                                <br>
                                <br>
                                <br>
                                <ul></ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-accordion">
                    <div class="item-accordion__header js-toggle-accordion">
                        <span class="item-accordion__header-inner">У меня не отображаются марки в личном кабинете. Что делать?</span>
                    </div>
                    <div class="item-accordion__block js-dropdown-block">
                        <div class="item-accordion__block-content">
                            <div class="item-accordion__block-text">
                                <b>Напишите нам письмо на </b><b><a href="mailto:welcome@4lapy.ru">welcome@4lapy.ru</a><br>
                                </b><br>
                                В письме укажите Ваш номер мобильного телефона и номер бонусной карты (если есть).<br>
                                Для ускорения начисления марок рекомендуем указать в письме за какие покупки&nbsp;не хватает марок (например, дату и сумму покупки)<br>
                                Мы оперативно исправим ошибку!<br>
                                <br>
                                <br>
                                <br>
                                <br>
                                <ul></ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item-accordion">
                    <div class="item-accordion__header js-toggle-accordion">
                        <span class="item-accordion__header-inner">Посмотреть полные условия акции</span>
                    </div>
                    <div class="item-accordion__block js-dropdown-block">
                        <div class="item-accordion__block-content">
                            <div class="item-accordion__block-text">
                                Посмотреть полные условия акции <u><span style="color: #005951;"><b><a target="_blank" href="https://4lapy.ru/home/img/Правила_Акции_Уютно_жить_октябрь2019.pdf">по ссылке.</a></b></span></u><br>
                                <br>
                                <ul></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
