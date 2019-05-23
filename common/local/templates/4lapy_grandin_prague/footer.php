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
    <section data-id-section-landing="prizes" class="prizes-landing">
        <div class="container-landing">
            <div class="landing-title">
                Призы
            </div>
            <div class="prizes-landing__list prizes-landing__list--grandin-prague">
                <div class="item">
                    <div class="item-card item-card--bg-brown">
                        <div  class="item-card__img" style="background-image: url('/img/prizes-grandin-prague1.png')"></div>
                        <div class="item-card__info">
                            100&nbsp;бонусов каждому<br class="hidden-mobile" /> после регистрации покупки.
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="item-card item-card--bg-brown">
                        <div  class="item-card__img" style="background-image: url('/img/prizes-grandin-prague2.png')"></div>
                        <div class="item-card__info">
                            Электронные мерные&nbsp;ложки&nbsp;– <br class="hidden-mobile" /> всего&nbsp;150&nbsp;призов.
                            <br class="hidden-mobile" />
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="item-card item-card--prague">
                        <div  class="item-card__img"></div>
                        <div class="item-card__info">
                            Путешествие в&nbsp;Прагу<br class="hidden-mobile" /> на&nbsp;двоих&nbsp;– 1&nbsp;приз.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section data-id-section-landing="where-buy" class="where-buy-landing">
        <div class="where-buy-landing__map" id="mapWhereBuylanding" data-map-where-buy-landing="dark"></div>
    </section>

    <?if(!$USER->IsAuthorized()) {?>
    <section id="info-prizes" data-id-section-landing="info-prizes" class="info-prizes">
        <div class="container-landing">
            <div class="info-prizes__message">
                <div class="landing-title landing-title_white">
                    Регистрируйте чеки<br class="hidden-mobile" /> и&nbsp;выигрывайте призы каждую неделю
                </div>
            </div>
        </div>
    </section>
    <? } ?>

    <section data-id-section-landing="winners" class="winners-landing winners-landing--grandin-prague" style="background-image: url('/img/bg-winners-grandin-prague.png')">
        <div class="winners-landing__spoon" style="background-image: url('/img/winners-spoon-grandin-prague.png')"></div>

        <div class="winners-landing__container">
            <div class="winners-landing__bg-left" style="background-image: url('/img/winners-left-grandin-prague.png')"></div>
            <div class="winners-landing__bg-right" style="background-image: url('/img/winners-right-grandin-prague.png')"></div>
            <div class="container-landing">
                <?php
                $APPLICATION->IncludeComponent('articul:action.winners', '', [
                    "SECTION_CODE" => 'GRANDIN'
                ]);
                ?>
            </div>
        </div>
    </section>


    <section data-id-section-landing="contacts" class="feedback-landing" data-wrap-form-feedback-landing="true">
        <div class="feedback-landing__container container-landing">
            <div class="landing-title landing-title_dark">
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

<footer class="footer-landing">
    <div class="container-landing">
        <div class="footer-landing__content">
            <div class="footer-landing__share">
                <div class="footer-landing__share-title">
                    <span class="icon">
                        <?= new SvgDecorator('icon-landing-share', 26, 24) ?>
                    </span>
                    Рассказать о нас
                </div>
                <div class="footer-landing__share-content">
                    <div class="ya-share2" 
                        data-services="vkontakte,facebook,odnoklassniki" 
                        data-title="Как выиграть запас корма Grandin на год вперед?"
                        data-description="Для участия в акции купите любой корм Grandin на сумму от 1800 рублей и зарегистрируйте покупку  на сайте акции grandin.4lapy.ru."
                        data-image="<?='https://'.$_SERVER['SERVER_NAME'].'/static/build/images/content/landing-grandin-share.png'?>"></div>
                </div>
            </div>
            <div class="footer-landing__copyright">
                © Grandin, сбалансированный рацион для кошек и собак
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
