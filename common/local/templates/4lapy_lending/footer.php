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
                        <li class="b-tab-title__item js-tab-item active">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-landing1">
                                <span class="b-tab-title__text">8.02</span>
                            </a>
                        </li>
                        <li class="b-tab-title__item js-tab-item">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-landing2">
                                <span class="b-tab-title__text">15.02</span>
                            </a>
                        </li>
                        <li class="b-tab-title__item js-tab-item">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-landing3">
                                <span class="b-tab-title__text">22.02</span>
                            </a>
                        </li>
                        <li class="b-tab-title__item js-tab-item">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-landing4">
                                <span class="b-tab-title__text">1.03</span>
                            </a>
                        </li>
                        <li class="b-tab-title__item js-tab-item">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-landing5">
                                <span class="b-tab-title__text">Главные призы</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="b-tab-content">
                    <div class="b-tab-content__container js-tab-content active" data-tab-content="winners-landing1">
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
                    <div class="b-tab-content__container js-tab-content" data-tab-content="winners-landing2">
                        <div class="winners-landing__list">
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Седов Максим">Седов Максим</div>
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
                                    <div class="item__name" title="Седов Максим">Седов Максим</div>
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
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Пронина Ирина">Пронина Ирина</div>
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
                                    <div class="item__name" title="Сандалова Александра">Сандалова Александра</div>
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
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Кириллов Олег">Кириллов Олег</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="b-tab-content__container js-tab-content " data-tab-content="winners-landing3">
                        <div class="winners-landing__list">
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Григорий Лялин">Григорий Лялин</div>
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
                                    <div class="item__name" title="Иванова Ольга">Иванова Ольга</div>
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
                                    <div class="item__name" title="Сандалова Александра">Сандалова Александра</div>
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
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="Кириллов Олег">Кириллов Олег</div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone">*(***)****356</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="b-tab-content__container js-tab-content" data-tab-content="winners-landing4">
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
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section data-id-section-landing="contacts" class="feedback-landing" data-wrap-form-feedback-landing="true">
        <div class="feedback-landing__container container-landing">
            <div class="landing-title landing-title_dark">
                Обратная связь
            </div>
            <form data-form-feedback-landing="true" class="form-landing feedback-landing__form js-form-validation" method="post" action="/" name="" enctype="multipart/form-data">
                <div class="form-group form-group_full">
                    <textarea id="QUESTION_REG_FEEDBACK_GRANDIN" name="QUESTION_REG_FEEDBACK_GRANDIN" value="" placeholder="напишите ваш вопрос"></textarea>
                    <div class="b-error">
                        <span class="js-message"></span>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" id="FIO_REG_FEEDBACK_GRANDIN" name="FIO_REG_FEEDBACK_GRANDIN" value="" placeholder="имя, фамилия">
                    <div class="b-error">
                        <span class="js-message"></span>
                    </div>
                </div>
                <div class="form-group">
                    <input type="tel" name="phone" value="" id="PHONE_REG_FEEDBACK_GRANDIN" placeholder="номер телефона">
                    <div class="b-error">
                        <span class="js-message"></span>
                    </div>
                </div>
                <div class="form-group">
                    <input type="email" id="EMAIL_REG_FEEDBACK_GRANDIN" name="EMAIL_REG_FEEDBACK_GRANDIN" value="" placeholder="e-mail" >
                    <div class="b-error">
                        <span class="js-message"></span>
                    </div>
                </div>
                <div class="feedback-landing__form-info">
                    Обратите внимание, что все поля данной формы должны быть заполнены
                </div>
                <div class="feedback-landing__btn-form">
                    <button type="submit" class="landing-btn">Отправить</button>
                </div>
            </form>

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
                    <a href="#" class="item item--vk">
                        <?= new SvgDecorator('icon-landing-vk', 46, 43) ?>
                    </a>
                    <a href="#" class="item item--fb">
                        <?= new SvgDecorator('icon-landing-fb', 22, 43) ?>
                    </a>
                    <a href="#" class="item item--ok">
                        <?= new SvgDecorator('icon-landing-ok', 23, 43) ?>
                    </a>
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

</body>
</html>
