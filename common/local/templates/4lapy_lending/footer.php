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
            <div class="prizes-landing__list">
                <div class="item">
                    <div class="item-card">
                        <div  class="item-card__img" style="background-image: url('/static/build/images/content/landing-prizes1.png')"></div>
                        <div class="item-card__info">
                            Миска Grandin керамическая 300мл*.<br class="hidden-mobile" /> Выдается за&nbsp;покупку корма Grandin от&nbsp;1800&nbsp;р.
                        </div>
                    </div>
                    <div class="item-note">*количество призов ограничено</div>
                </div>
                <div class="item">
                    <div class="item-card">
                        <div  class="item-card__img" style="background-image: url('/static/build/images/content/landing-prizes2.png')"></div>
                        <div class="item-card__info">
                            Запас сухого корма Grandin на&nbsp;2&nbsp;месяца*.<br class="hidden-mobile" /> Разыгрывается каждую пятницу &mdash; 8,&nbsp;15,&nbsp;22&nbsp;февраля и&nbsp;1&nbsp;марта.<br/> 
                            <span class="bold">Всего 40 призов</span>
                        </div>
                    </div>
                    <div class="item-note">*в&nbsp;виде начисления целевых бонусов на&nbsp;карту Четыре&nbsp;Лапы</div>
                </div>
                <div class="item">
                    <div class="item-card">
                        <div  class="item-card__img" style="background-image: url('/static/build/images/content/landing-prizes3.png')"></div>
                        <div class="item-card__info">
                            Главный приз - годовой запас сухого корма Grandin*. Разыгрывается 1&nbsp;марта.<br/>
                            <span class="bold">Всего 10&nbsp;призов</span>
                        </div>
                    </div>
                    <div class="item-note">*в&nbsp;виде начисления целевых бонусов на&nbsp;карту Четыре&nbsp;Лапы</div>
                </div>
            </div>
        </div>
    </section>

    <section data-id-section-landing="where-buy" class="where-buy-landing">
        <div class="where-buy-landing__map" id="mapWhereBuylanding" data-map-where-buy-landing="true"></div>
    </section>



    <section data-id-section-landing="winners" class="winners-landing" style="background-image: url('/static/build/images/content/bg-splash-landing.png')">
        <div class="winners-landing__bg-left" style="background-image: url('/static/build/images/content/landing-winners-left.png')"></div>
        <div class="winners-landing__bg-right" style="background-image: url('/static/build/images/content/landing-winners-right.png')"></div>
        <div class="container-landing">
            <div class="landing-title landing-title_white">
                Победители
            </div>
            <div class="b-tab winners-landing__content">
                <div class="b-tab-title">
                    <ul class="b-tab-title__list">
                        <li class="b-tab-title__item js-tab-item">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-landing1">
                                <span class="b-tab-title__text">8.02</span>
                            </a>
                        </li>
                        <li class="b-tab-title__item js-tab-item">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-landing2">
                                <span class="b-tab-title__text">15.02</span>
                            </a>
                        </li>
                        <li class="b-tab-title__item js-tab-item active">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-landing3">
                                <span class="b-tab-title__text">22.02</span>
                            </a>
                        </li>
                        <?/**<li class="b-tab-title__item js-tab-item">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-landing4">
                                <span class="b-tab-title__text">1.03</span>
                            </a>
                        </li>
                        <li class="b-tab-title__item js-tab-item">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-landing5">
                                <span class="b-tab-title__text">Главные призы</span>
                            </a>
                        </li>**/ ?>
                    </ul>
                </div>
                <div class="b-tab-content">
                    <div class="b-tab-content__container js-tab-content" data-tab-content="winners-landing1">
                        <div class="winners-landing__list">
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Строгонова Светлана">Строгонова Светлана</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****762</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Сериков Сергей">Сериков Сергей</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****654</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Москва Алина">Москва Алина</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****334</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Сергеева Светлана">Сергеева Светлана</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****625</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Прокофьева светлана">Прокофьева светлана</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****311</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Ляпина Татьяна">Ляпина Татьяна</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****967</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Мелентьева Ксения">Мелентьева Ксения</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****855</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="гиззатуллина окасана">гиззатуллина окасана</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****177</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Казаков Владимир">Казаков Владимир</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****059</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Petrova Anna">Petrova Anna</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****307</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="b-tab-content__container js-tab-content" data-tab-content="winners-landing2">
                        <div class="winners-landing__list">
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Федоткина Татьяна">Федоткина Татьяна</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****213</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Новичкова Юлия">Новичкова Юлия</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****861</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Павлихин Артем">Павлихин Артем</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****322</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Григорчук Илья">Григорчук Илья</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****463</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Мещеряков Олег">Мещеряков Олег</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****741</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Сафронова Альбина">Сафронова Альбина</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****694</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Ионова Наталья">Ионова Наталья</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****419</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Писарева Елена">Писарева Елена</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****687</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Александрова Елена">Александрова Елена</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****712</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Орехова Анна">Орехова Анна</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****377</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="b-tab-content__container js-tab-content active" data-tab-content="winners-landing3">
                        <div class="winners-landing__list">
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Шувалова Яна">Шувалова Яна</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****071</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Зубов Вячеслав">Зубов Вячеслав</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****083</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Лисина Елена">Лисина Елена</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****434</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Шерматова Раиса">Шерматова Раиса</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****985</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Афонасьев Владимир">Афонасьев Владимир</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****972</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Конюхова Ольга">Конюхова Ольга</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****803</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Фадеева Марина">Фадеева Марина</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****628</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Штин Анастасия">Штин Анастасия</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****004</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Соловьева Инна">Соловьева Инна</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****237</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Шмелева Екатерина">шмелева екатерина</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****998</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?/**<div class="b-tab-content__container js-tab-content" data-tab-content="winners-landing4">
                        <div class="winners-landing__list">
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Иванова Ольга">Иванова Ольга</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Седов Максим">Седов Максим</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Кириллов Олег">Кириллов Олег</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Григорий Лялин">Григорий Лялин</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Сандалова Александра">Сандалова Александра</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Пронина Ирина">Пронина Ирина</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Иванова Ольга">Иванова Ольга</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="b-tab-content__container js-tab-content " data-tab-content="winners-landing5">
                        <div class="winners-landing__list">
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Иванова Ольга">Иванова Ольга</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Седов Максим">Седов Максим</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Кириллов Олег">Кириллов Олег</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Григорий Лялин">Григорий Лялин</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Сандалова Александра">Сандалова Александра</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Пронина Ирина">Пронина Ирина</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Иванова Ольга">Иванова Ольга</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                        </div>
                    </div>**/ ?>
                </div>
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
