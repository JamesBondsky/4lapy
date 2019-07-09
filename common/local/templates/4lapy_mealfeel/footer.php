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
    <section data-id-section-landing="prizes" class="prizes-mealfeel">
        <div class="container-landing">
            <div class="landing-title">
                Призы
            </div>
            <div class="prizes-mealfeel__list">
                <div class="item">
                    <div class="item-card">
                        <div class="item-card__img-wrap">
                            <div class="item-card__img item-card__img_tank" style="background-image: url('/img/prizes1.png')"></div>
                        </div>
                        <div class="item-card__title">Бак для корма</div>
                        <div class="item-card__descr">Выдается за&nbsp;покупку корма Mealfeel (сухого вместе с&nbsp;влажным) на&nbsp;сумму&nbsp;от&nbsp;1500&nbsp;р.<br /> Бак вмещает 3 кг&nbsp;корма.</div>
                    </div>
                </div>
                <div class="item">
                    <div class="item-card">
                        <div class="item-card__img-wrap">
                            <div class="item-card__img item-card__img_devices" style="background-image: url('/img/prizes2.png')"></div>
                        </div>
                        <div class="item-card__title">Девайсы для правильного&nbsp;питания</div>
                        <div class="item-card__descr">
                            В&nbsp;розыгрыше новые подарки каждую неделю:
                            <ul>
                                <li><b>5&nbsp;июля</b>&nbsp;&mdash; мультиварка: готовит правильно и&nbsp;быстро</li>
                                <li><b>12&nbsp;июля</b>&nbsp;&mdash; соковыжималка: фреш каждый день</li>
                                <li><b>19&nbsp;июля</b>&nbsp;&mdash; фильтр для воды: чистая вода&nbsp;&mdash; основа здоровья</li>
                                <li><b>26&nbsp;июля</b>&nbsp;&mdash; блендер: смузи и&nbsp;коктейли каждый день</li>
                                <li><b>1&nbsp;августа</b>&nbsp;&mdash; умный сад: свежая зелень круглый год</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="item-card">
                        <div class="item-card__img-wrap">
                            <div class="item-card__img item-card__img_spa" style="background-image: url('/img/prizes3.png')"></div>
                        </div>
                        <div class="item-card__title"><nobr>SPA-Weekend</nobr></div>
                        <div class="item-card__descr"><nobr>SPA-weekend</nobr> на&nbsp;курорте Роза Хутор в&nbsp;Сочи на&nbsp;двоих. Срок поездки&nbsp;&mdash; 3 дня 2&nbsp;ночи. Розыгрыш 1&nbsp;августа.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="info-prizes" data-id-section-landing="info-prizes" class="info-prizes info-prizes_mealfeel">
        <div class="container-landing">
            <div class="info-prizes__message">
                Регистрируйте чеки и&nbsp;выигрывайте!<br />
                Новые подарки каждую неделю!<br />
                50 победителей + 1 главный приз!
            </div>
        </div>
    </section>

    <section data-id-section-landing="where-buy" class="where-buy-landing">
        <div class="where-buy-landing__map" id="mapWhereBuylanding" data-map-where-buy-landing="dark"></div>
    </section>

    <section data-id-section-landing="winners" class="winners-landing winners-landing_mealfeel" style="background-image: url('/img/bg-triangles.jpg'); <?$APPLICATION->ShowViewContent('empty-winners');?>">
        <div class="container-landing">
            <?php
            $APPLICATION->IncludeComponent('articul:action.winners', '', [
                "SECTION_CODE" => 'MEALFEEL'
            ]);
            ?>
        </div>
    </section>


    <section data-id-section-landing="contacts" class="feedback-landing feedback-landing_mealfeel" data-wrap-form-feedback-landing="true">
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

<footer class="footer-landing-mealfeel">
    <div class="container-landing">
        <div class="footer-landing-mealfeel__content">
            <div class="footer-landing-mealfeel__logo">
                <img src="/img/mealfeel-logo.svg" alt="Mealfeel" title="Mealfeel"/>
            </div>
            <div class="footer-landing-mealfeel__copyright footer-landing-mealfeel__copyright_mobile">
                &copy; 2019 Mealfeel, гармония&nbsp;питания&nbsp;как&nbsp;по&nbsp;нотам
            </div>
            <div class="footer-landing-mealfeel__share">
                <div class="ya-share2"
                     data-services="facebook,odnoklassniki,vkontakte"
                     data-title="Выиграйте SPA-weekend, Роза Хутор Сочи"
                     data-description="Купите Mealfeel, регистрируйтесь и проверяйте результаты розыгрыша каждую пятницу июля. В розыгрыше 50 призов для правильного питания. Главный приз разыгрывается 1 августа. Удачи!"
                     data-image="<?='https://'.$_SERVER['SERVER_NAME'].'/img/mealfeel-share.png'?>"></div>
            </div>
            <div class="footer-landing-mealfeel__copyright footer-landing-mealfeel__copyright_desktop">
                &copy; 2019 Mealfeel, гармония&nbsp;питания&nbsp;как&nbsp;по&nbsp;нотам
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
