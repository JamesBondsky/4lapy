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
    <section data-id-section-landing="prizes" class="prizes-landing prizes-landing_gray prizes-landing_canin">
        <div class="container-landing">
            <div class="landing-title landing-title_gray-dark">
                Призы
            </div>
            <div class="prizes-landing__list">
                <div class="item">
                    <div class="item-card">
                        <div  class="item-card__img" style="background-image: url('/static/build/images/content/canin-prizes1.png')"></div>
                        <div class="item-card__info">
                            150 баллов на&nbsp;карту “ЧЕТЫРЕ ЛАПЫ”*
                        </div>
                    </div>
                    <div class="item-note">
                        <span>*Начисляются за&nbsp;покупку породной линейки ROYAL CANIN<sup><small>&reg;</small></sup> на&nbsp;сумму от&nbsp;1000&nbsp;рублей</span>
                    </div>
                </div>
                <div class="item">
                    <div class="item-card">
                        <div  class="item-card__img" style="background-image: url('/static/build/images/content/canin-prizes2.png')"></div>
                        <div class="item-card__info">
                            Переноска для путешествия с&nbsp;животными*.<br class="hidden-mobile" />
                            Разыгрывается каждую неделю**.<br class="hidden-mobile" />
                            <nobr>Всего&nbsp;&mdash; 56 призов</nobr>
                        </div>
                    </div>
                    <div class="item-note">
                        <span>*в&nbsp;виде начисления целевых бонусов<br class="hidden-mobile" /> на&nbsp;карту Четыре Лапы<br /></span>
                        <span>**15, 22, 29 апреля,<br /> 06, 13, 20, 27&nbsp;мая,<br /> 03 июня</span>
                    </div>
                </div>
                <div class="item">
                    <div class="item-card">
                        <div  class="item-card__img" style="background-image: url('/static/build/images/content/canin-prizes3.png')"></div>
                        <div class="item-card__info">
                            Главный приз&nbsp;&mdash; Поездка на&nbsp;родину породы*.<br class="hidden-mobile" />
                            Разыгрывается 03 июня.<br class="hidden-mobile" />
                            <nobr>Всего&nbsp;&mdash; 1 приз</nobr>
                        </div>
                    </div>
                    <div class="item-note">
                        <span>*в&nbsp;виде подарочного сертификата на&nbsp;120&nbsp;000&nbsp;рублей в&nbsp;туристическое агентство.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section data-id-section-landing="where-buy" class="where-buy-landing">
        <div class="where-buy-landing__map" id="mapWhereBuylanding" data-map-where-buy-landing="gray-dark"></div>
    </section>

    <section data-id-section-landing="winners" class="winners-canin">
        <?php
            $APPLICATION->IncludeComponent('articul:action.winners', '', [
                    "SECTION_CODE" => 'ROYAL_CANIN'
            ]);
            ?>
    </section>


    <section data-id-section-landing="contacts" class="feedback-landing feedback-landing_canin feedback-landing_gray" data-wrap-form-feedback-landing="true">
        <div class="feedback-landing__container container-landing">
            <div class="landing-title landing-title_gray">
                Обратная связь
            </div>

            <?php
                $APPLICATION->IncludeComponent(
                    'bitrix:form.result.new',
                    'feedback',
                    [
                        'CACHE_TIME'             => '3600000',
                        'CACHE_TYPE'             => 'A',
                        'CHAIN_ITEM_LINK'        => '',
                        'CHAIN_ITEM_TEXT'        => '',
                        'EDIT_URL'               => '',
                        'IGNORE_CUSTOM_TEMPLATE' => 'Y',
                        'LIST_URL'               => '',
                        'SEF_MODE'               => 'N',
                        'SUCCESS_URL'            => '',
                        'USE_EXTENDED_ERRORS'    => 'Y',
                        'VARIABLE_ALIASES'       => [
                            'RESULT_ID'   => 'RESULT_ID',
                            'WEB_FORM_ID' => 'WEB_FORM_ID',
                        ],
                        'WEB_FORM_ID'            => \FourPaws\Helpers\FormHelper::getIdByCode(\FourPaws\Enum\Form::FEEDBACK),
                    ]
                );
            ?>

            <div class="registr-check-landing__response" data-response-form-landing="true"></div>
        </div>
    </section>

</div>

<footer class="footer-canin">
    <div class="container-landing">
        <div class="footer-canin__content">
            <div class="footer-canin__copyright">
                <div class="item">© Royal Canin®</div>
                <div class="item">© Четыре лапы®</div>
            </div>
            <div class="footer-canin__primary">
                Общий срок акции с&nbsp;<nobr>08.04.19</nobr> по&nbsp;<nobr>02.07.19</nobr>. Срок приобретения товаров и&nbsp;регистрации чеков с&nbsp;<nobr>08.04.19</nobr> по&nbsp;<nobr>02.06.19</nobr>. <nobr>Кол-во</nobr> подарков ограничено. Акция действует при наличии товара в&nbsp;магазине. Для участия в&nbsp;акции сохраняйте чек. Подробную информацию об&nbsp;организаторе акции, правилах ее&nbsp;проведения, количестве призов, сроках, месте и&nbsp;порядке их&nbsp;получения можно узнать по&nbsp;телефону бесплатной горячей линии <nobr>8 800 770-00-22</nobr> или на&nbsp;сайте royalcanin.4lapy.ru. Внешний вид подарков может отличаться.
            </div>
        </div>
    </div>
</footer>


<div class="b-shadow js-shadow"></div>
<div class="b-shadow b-shadow--popover js-open-shadow"></div>
</div>
<?php require_once __DIR__ . '/blocks/footer/popups.php' ?>
<script src="<?= $markup->getJsFile() ?>"></script>

</body>
</html>
