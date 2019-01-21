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

<div class="bottom-lending">
    <section data-id-section-lending="prizes" class="prizes-lending">
        <div class="container-lending">
            <div class="lending-title">
                Призы
            </div>
            <div class="prizes-lending__list">
                <div class="item">
                    <div class="item-card">
                        <div  class="item-card__img" style="background-image: url('/static/build/images/content/lending-prizes1.png')"></div>
                        <div class="item-card__info">
                            Миска Grandin керамическая 300мл*.<br class="hidden-mobile" /> Выдается за&nbsp;покупку корма Grandin от&nbsp;1800&nbsp;р.
                        </div>
                    </div>
                    <div class="item-note">*количество призов ограничено</div>
                </div>
                <div class="item">
                    <div class="item-card">
                        <div  class="item-card__img" style="background-image: url('/static/build/images/content/lending-prizes2.png')"></div>
                        <div class="item-card__info">
                            Запас сухого корма Grandin на&nbsp;2&nbsp;месяца*.<br class="hidden-mobile" /> Разыгрывается каждую пятницу &mdash; 8,&nbsp;15,&nbsp;22&nbsp;февраля и&nbsp;1&nbsp;марта.<br/> 
                            <span class="bold">Всего 40 призов</span>
                        </div>
                    </div>
                    <div class="item-note">*в&nbsp;виде начисления целевых бонусов на&nbsp;карту Четыре&nbsp;Лапы</div>
                </div>
                <div class="item">
                    <div class="item-card">
                        <div  class="item-card__img" style="background-image: url('/static/build/images/content/lending-prizes3.png')"></div>
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

    <section data-id-section-lending="where-buy" class="where-buy-lending">
        <div class="where-buy-lending__map" id="mapWhereBuyLending" data-map-where-buy-lending="true"></div>
    </section>

    <section data-id-section-lending="winners" class="winners-lending" style="background-image: url('/static/build/images/content/bg-splash-lending.png')">
        <div class="winners-lending__bg-left" style="background-image: url('/static/build/images/content/lending-winners-left.png')"></div>
        <div class="winners-lending__bg-right" style="background-image: url('/static/build/images/content/lending-winners-right.png')"></div>
        <div class="container-lending">
            <div class="lending-title lending-title_white">
                Победители
            </div>
            <div class="b-tab winners-lending__content">
                <div class="b-tab-title">
                    <ul class="b-tab-title__list">
                        <li class="b-tab-title__item js-tab-item active">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-lending1">
                                <span class="b-tab-title__text">8.02</span>
                            </a>
                        </li>
                        <li class="b-tab-title__item js-tab-item">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-lending2">
                                <span class="b-tab-title__text">15.02</span>
                            </a>
                        </li>
                        <li class="b-tab-title__item js-tab-item">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-lending3">
                                <span class="b-tab-title__text">22.02</span>
                            </a>
                        </li>
                        <li class="b-tab-title__item js-tab-item">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-lending4">
                                <span class="b-tab-title__text">1.03</span>
                            </a>
                        </li>
                        <li class="b-tab-title__item js-tab-item">
                            <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-lending5">
                                <span class="b-tab-title__text">Главные призы</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="b-tab-content">
                    <div class="b-tab-content__container js-tab-content active" data-tab-content="winners-lending1">
                        <div class="winners-lending__list">
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
                    <div class="b-tab-content__container js-tab-content" data-tab-content="winners-lending2">
                        <div class="winners-lending__list">
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
                    <div class="b-tab-content__container js-tab-content " data-tab-content="winners-lending3">
                        <div class="winners-lending__list">
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
                    <div class="b-tab-content__container js-tab-content" data-tab-content="winners-lending4">
                        <div class="winners-lending__list">
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
                    <div class="b-tab-content__container js-tab-content " data-tab-content="winners-lending5">
                        <div class="winners-lending__list">
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

    <section data-id-section-lending="contacts" class="feedback-lending" data-wrap-form-feedback-lending="true">
        <div class="feedback-lending__container container-lending">
            <div class="lending-title lending-title_dark">
                Обратная связь
            </div>
            <form data-form-feedback-lending="true" class="form-lending feedback-lending__form" method="post" action="/" name="" enctype="multipart/form-data">
                <div class="form-group form-group_full">
                    <textarea id="QUESTION_REG_FEEDBACK_GRANDIN" name="QUESTION_REG_FEEDBACK_GRANDIN" value="" placeholder="напишите ваш вопрос" required></textarea>
                </div>
                <div class="form-group">
                    <input type="text" id="FIO_REG_FEEDBACK_GRANDIN" name="FIO_REG_FEEDBACK_GRANDIN" value="" placeholder="имя, фамилия" required >
                </div>
                <div class="form-group">
                    <input type="text" id="PHONE_REG_FEEDBACK_GRANDIN" name="PHONE_REG_FEEDBACK_GRANDIN" value="" placeholder="номер телефона" required >
                </div>
                <div class="form-group">
                    <input type="email" id="EMAIL_REG_FEEDBACK_GRANDIN" name="EMAIL_REG_FEEDBACK_GRANDIN" value="" placeholder="e-mail" required >
                </div>
                <div class="feedback-lending__form-info">
                    Обратите внимание, что все поля данной формы должны быть заполнены
                </div>
                <div class="feedback-lending__btn-form">
                    <input type="submit" class="lending-btn" value="Отправить">
                </div>
            </form>

            <div class="registr-check-lending__response" data-response-form-lending="true"></div>
        </div>
    </section>
</div>

<footer class="footer-lending">
    <div class="container-lending">
        <div class="footer-lending__content">
            <div class="footer-lending__share">
                <div class="footer-lending__share-title">
                    <span class="icon">
                        <?= new SvgDecorator('icon-lending-share', 26, 24) ?>
                    </span>
                    Рассказать о нас
                </div>
                <div class="footer-lending__share-content">
                    <a href="#" class="item item--vk">
                        <?= new SvgDecorator('icon-lending-vk', 46, 43) ?>
                    </a>
                    <a href="#" class="item item--fb">
                        <?= new SvgDecorator('icon-lending-fb', 22, 43) ?>
                    </a>
                    <a href="#" class="item item--inst">
                        <?= new SvgDecorator('icon-lending-inst', 37, 43) ?>
                    </a>
                    <a href="#" class="item item--ok">
                        <?= new SvgDecorator('icon-lending-ok', 23, 43) ?>
                    </a>
                </div>
            </div>
            <div class="footer-lending__copyright">
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
