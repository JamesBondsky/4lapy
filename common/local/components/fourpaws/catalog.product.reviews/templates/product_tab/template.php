<?php
/**
 * @var array $arParams
 * @var array $arResult
 */

use FourPaws\App\Templates\ViewsEnum;
use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var CBitrixComponentTemplate $this
 */
$this->SetViewTarget(ViewsEnum::PRODUCT_RATING_TAB_VIEW)
?>
    <div class="b-tab-content__container js-tab-content" data-tab-content="reviews">
        <div class="tab-content-review">
            <div class="b-rate-block">
                <div class="b-rate-block__left-side">
                    <p class="b-rate-block__name">Рейтинг</p>
                </div>
                <div class="b-rate-block__right-side">
                    <div class="b-rate-block__rate-wrapper">
                        <div class="b-rating b-rating--big">
                            <div class="b-rating__star-block b-rating__star-block--active">
                                <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                            </div>
                            <div class="b-rating__star-block b-rating__star-block--active">
                                <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                            </div>
                            <div class="b-rating__star-block b-rating__star-block--active">
                                <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                            </div>
                            <div class="b-rating__star-block b-rating__star-block--active">
                                <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                            </div>
                            <div class="b-rating__star-block">
                                <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                            </div>
                        </div>
                        <span class="b-rate-block__rate-description">на основе 12 отзывов</span>
                    </div>
                    <button class="b-button b-button--link-feedback js-add-review">Оставить отзыв</button>
                </div>
            </div>
            <form class="b-form-review js-form-review">
                <div class="b-form-review__wrapper-blocks">
                    <p class="b-form-review__text-block b-form-review__text-block--account">
                        Укажите телефон или почту, а также пароль, если у вас есть аккаун на нашем сайте
                    </p>
                    <label class="b-form-review__label" for="id-review-tel">Мобильный телефон</label>
                    <input class="b-form-review__input" id="id-review-tel" type="tel" name="tel"
                           value=""/>
                    <label class="b-form-review__label" for="id-review-mail">Эл. почта</label>
                    <input class="b-form-review__input" id="id-review-mail" type="email" name="email"
                           value=""/>
                    <label class="b-form-review__label" for="id-review-pass">Пароль</label>
                    <input class="b-form-review__input" id="id-review-pass" type="password" name="pass"
                           value="" autocomplete="off"/>
                </div>
                <div class="b-form-review__wrapper-blocks">
                    <h4 class="b-form-review__sub-heading">Оценка</h4>
                    <div class="b-rating b-rating--large b-rating--form-review">
                        <div class="b-rating__form">
                            <div class="b-rating__group">
                                <input class="b-rating__input" type="radio" id="radio5" name="undefined"
                                       value="5"/>
                                <label class="b-rating__star" for="radio5">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star-stroke', 13, 12) ?></span>
                                </label>
                                <input class="b-rating__input" type="radio" id="radio4" name="undefined"
                                       value="4"/>
                                <label class="b-rating__star" for="radio4">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star-stroke', 13, 12) ?></span>
                                </label>
                                <input class="b-rating__input" type="radio" id="radio3" name="undefined"
                                       value="3"/>
                                <label class="b-rating__star" for="radio3">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star-stroke', 13, 12) ?></span>
                                </label>
                                <input class="b-rating__input" type="radio" id="radio2" name="undefined"
                                       value="2"/>
                                <label class="b-rating__star" for="radio2">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star-stroke', 13, 12) ?></span>
                                </label>
                                <input class="b-rating__input" type="radio" id="radio1" name="undefined"
                                       value="1"/>
                                <label class="b-rating__star" for="radio1">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star-stroke', 13, 12) ?></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <h4 class="b-form-review__sub-heading">Отзыв</h4>
                    <textarea class="b-form-review__textarea" name="text" required="required"
                              placeholder="Оставьте ваш отзыв:"></textarea>
                    <button class="b-button b-button--form-review">Отправить</button>
                </div>
                <div class="b-form-review__wrapper-blocks">
                    <p class="b-form-review__text-block">Ваш комментарий успешно отправлен, он появится
                        здесь после проверки</p>
                </div>
            </form>
            <div class="b-review">
                <h2 class="b-review__heading">Отзывы</h2>
                <ul class="b-review__list">
                    <li class="b-review__item">
                        <header class="b-review__left-side">
                            <p class="b-review__name">Андрей</p>
                            <p class="b-review__date">15 июля 2017</p>
                        </header>
                        <div class="b-review__right-side">
                            <div class="b-rating b-rating--big">
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                            </div>
                            <div class="b-review__text">
                                <p>После переноса производства кормов Royal Canin в Россию качество
                                    моментально упало. Корм приобрел сильный запах дешевого Вискаса.
                                    Животные с дикими глазами бегут на этот &quot;аромат&quot;, но после
                                    еды чувствуют себя плохо. Аллергичная собака чешется от него еще
                                    сильнее.</p>
                            </div>
                        </div>
                    </li>
                    <li class="b-review__item">
                        <header class="b-review__left-side">
                            <p class="b-review__name">Сергей Михайлович Иванов</p>
                            <p class="b-review__date">15 июля 2017</p>
                        </header>
                        <div class="b-review__right-side">
                            <div class="b-rating b-rating--big">
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                            </div>
                            <div class="b-review__text">
                                <p>Впервые воспользовался услугами интернет-магазина. Сделал пробную
                                    покупку. Все отлично. Обещали доставить товар 18.08. По факту
                                    получил 05.08. Закажу ещё. Дешевле, чем в зоомагазине на 20-30%.</p>
                            </div>
                        </div>
                    </li>
                    <li class="b-review__item">
                        <header class="b-review__left-side">
                            <p class="b-review__name">Мария</p>
                            <p class="b-review__date">15 июля 2017</p>
                        </header>
                        <div class="b-review__right-side">
                            <div class="b-rating b-rating--big">
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                            </div>
                            <div class="b-review__text">
                                <p>Уж не знаю, за что нам такое, но собачка которую мы взяли несколько
                                    лет назад оказалась аллергенна и выражалось это исключительно на
                                    кожных покровах. Чуть что, не то съест - сразу высыпания и зуд -
                                    собака
                                    начинала чесаться обо все подряд. Долго лечили, выясняли. В итоге
                                    пришли к корму от Роял Канин</p>
                            </div>
                        </div>
                    </li>
                    <li class="b-review__item">
                        <header class="b-review__left-side">
                            <p class="b-review__name">Ольга</p>
                            <p class="b-review__date">15 июля 2017</p>
                        </header>
                        <div class="b-review__right-side">
                            <div class="b-rating b-rating--big">
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                            </div>
                            <div class="b-review__text">
                                <p>ткие надежды возлагала на этот корм. собаке понравился, но на второй
                                    день она вся покраснела (у нас голая кхс), стала чесаться и
                                    поносить( кормила неделю - надеялась адаптируется. но нет(</p>
                            </div>
                        </div>
                    </li>
                    <li class="b-review__item">
                        <header class="b-review__left-side">
                            <p class="b-review__name">Катерина Елистратова</p>
                            <p class="b-review__date">29 сентября 2016</p>
                        </header>
                        <div class="b-review__right-side">
                            <div class="b-rating b-rating--big">
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                                <div class="b-rating__star-block b-rating__star-block--active">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                            </div>
                            <div class="b-review__text">
                                <p>до этого были на про плане для чувствительной кожи,(собака аллергик)
                                    и пока корм был французский всё было хорошо.... но радость была не
                                    долгой и вот корм стал российским и по собачке это стало заметно,(да
                                    и цена на него выросла) перешли на роял вроде тоже российский но....
                                    качество вроде держат. на нем уже 2 месяца и ттт всё хорошо. шерстью
                                    обрасли, лоснимся и весело скачем, да и какульки стали заметнее
                                    меньше, запаха почти нет, в отличии от российского проплана.Что
                                    будет дальше покажет время, а пока Роял рулит)))</p>
                            </div>
                        </div>
                    </li>
                </ul>
                <button class="b-button b-button--review js-add_review">Ещё отзывы</button>
            </div>
        </div>
    </div>
<?php
$this->EndViewTarget();

$this->SetViewTarget(ViewsEnum::PRODUCT_RATING_STARS_VIEW);
?>
    <div class="b-rating b-rating--card">
        <div class="b-rating__star-block b-rating__star-block--active">
            <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
        </div>
        <div class="b-rating__star-block b-rating__star-block--active">
            <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
        </div>
        <div class="b-rating__star-block b-rating__star-block--active">
            <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
        </div>
        <div class="b-rating__star-block b-rating__star-block--active">
            <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
        </div>
        <div class="b-rating__star-block b-rating__star-block--active">
            <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
        </div>
    </div>
    <span class="b-common-item__rank-text b-common-item__rank-text--card b-common-item__rank-text--review">На основе <span
                class="b-common-item__rank-num">12</span> отзывов</span>
<?php
$this->EndViewTarget();

$this->SetViewTarget(ViewsEnum::PRODUCT_RATING_TAB_HEADER_VIEW);
?>
    <li class="b-tab-title__item">
        <a class="b-tab-title__link js-tab-link"
           href="javascript:void(0);" title="Отзывы"
           data-tab="reviews">
            <span class="b-tab-title__text">Отзывы<span class="b-tab-title__number">(12)</span></span>
        </a>
    </li>
<?php
$this->EndViewTarget();

