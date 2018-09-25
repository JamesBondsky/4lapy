<?php
/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arParams
 * @var array                     $arResult
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Templates\ViewsEnum;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;

$uniqueCommentString = $arParams['TYPE'] . '_' . $arParams['HL_ID'] . '_' . $arParams['OBJECT_ID'];

$iconStar12x12 = new SvgDecorator('icon-star', 12, 12);
$worstRaitingValue = 1;
$bestRaitingValue = 5;

/**
 * Если нет оценок, то через микроразметку передаётся, что товар на 5 звёзд по одному отзыву.
 */
$ratingValue = $bestRaitingValue;
$ratingCount = 1;

if (
    $arResult['RATING'] >= $worstRaitingValue
    && $arResult['RATING'] <= $bestRaitingValue
    && $arResult['COUNT_COMMENTS'] > 0
) {
    $ratingValue = (float)$arResult['RATING'];
    $ratingCount = (int)$arResult['COUNT_COMMENTS'];
}

/** top catalog review block */
$this->SetViewTarget(ViewsEnum::PRODUCT_RATING_TAB_HEADER_VIEW) ?>
<li class="b-tab-title__item js-tab-item">
    <a class="b-tab-title__link js-tab-link"
       href="javascript:void(0);"
       title="Отзывы"
       data-tab="reviews">
        <span class="b-tab-title__text">
            Отзывы
            <span class="b-tab-title__number">(<?= $arResult['COUNT_COMMENTS'] ?>)</span>
        </span>
    </a>
</li>
<?php $this->EndViewTarget();
/** top catalog review block */
$this->SetViewTarget(ViewsEnum::PRODUCT_RATING_STARS_VIEW);

/**
 * AggregateRating microdata
 */
?>
<span itemprop="aggregateRating"
      itemscope
      itemtype="http://schema.org/AggregateRating"
      style="display: none;">
    <meta itemprop="worstRating" content="<?= $worstRaitingValue ?>">
    <meta itemprop="bestRating" content="<?= $bestRaitingValue ?>">
    <meta itemprop="ratingValue" content="<?= $ratingValue ?>">
    <meta itemprop="ratingCount" content="<?= $ratingCount ?>">
    <meta itemprop="reviewCount" content="<?= $ratingCount ?>">
</span>

<div class="b-rating b-rating--card" >
    <?php for ($i = $worstRaitingValue; $i <= $bestRaitingValue; $i++) {
        $activeClass = $arResult['RATING'] >= $i ? ' b-rating__star-block--active' : '';
        ?>
        <div class="b-rating__star-block <?= $activeClass ?>" >
            <span class="b-icon"><?= $iconStar12x12 ?></span>
        </div>
    <?php } ?>
</div>
<span class="b-common-item__rank-text b-common-item__rank-text--card b-common-item__rank-text--review">На основе <span
            class="b-common-item__rank-num"><?= $arResult['COUNT_COMMENTS'] ?></span> <?= WordHelper::declension(
        $arResult['COUNT_COMMENTS'],
        [
            'отзыва',
            'отзывов',
            'отзывов',
        ]
    ) ?></span>
<?php $this->EndViewTarget() ?>
<div class="b-tab-content__container js-tab-content" data-tab-content="reviews">
    <div class="tab-content-review">
        <?php /** @noinspection PhpUnhandledExceptionInspection */
        $frame = $this->createFrame()->begin();
        if ($arResult['COUNT_COMMENTS'] === 0) { ?>
            <div class="b-comment-block__info--block">
                <p class="b-comment-block__info">Пока никто не оставил комментарии.</p>
            </div>
        <?php } ?>
        <?php /** @noinspection PhpUnhandledExceptionInspection */
        $frame->beginStub(); ?>
        <div class="b-comment-block__info--block">
            <p class="b-comment-block__info">Пока никто не оставил комментарии.</p>
        </div>
        <?php /** @noinspection PhpUnhandledExceptionInspection */
        $frame->end(); ?>
        <div class="b-rate-block">
            <?php /** @noinspection PhpUnhandledExceptionInspection */
            $frame = $this->createFrame()->begin();
            if ($arResult['COUNT_COMMENTS'] > 0) { ?>
                <div class="b-rate-block__left-side">
                    <p class="b-rate-block__name">Рейтинг</p>
                </div>
                <?php
            } ?>
            <?php /** @noinspection PhpUnhandledExceptionInspection */
            $frame->end(); ?>
            <div class="b-rate-block__right-side">
                <?php /** @noinspection PhpUnhandledExceptionInspection */
                $frame = $this->createFrame()->begin('');
                if ($arResult['COUNT_COMMENTS'] > 0) { ?>
                    <div class="b-rate-block__rate-wrapper">
                        <div class="b-rating b-rating--big">
                            <?php for ($i = 1; $i <= 5; $i++) { ?>
                                <div class="b-rating__star-block<?= $arResult['RATING']
                                > $i ? ' b-rating__star-block--active' : '' ?>">
                                    <span class="b-icon"><?= new SvgDecorator('icon-star', 12, 12) ?></span>
                                </div>
                            <?php } ?>
                        </div>
                        <span class="b-rate-block__rate-description">на основе <?= $arResult['COUNT_COMMENTS'] ?>
                            <?= WordHelper::declension(
                                $arResult['COUNT_COMMENTS'],
                                [
                                    'отзыва',
                                    'отзывов',
                                    'отзывов',
                                ]
                            ) ?>
                        </span>
                    </div>
                <?php }
                /** @noinspection PhpUnhandledExceptionInspection */
                $frame->end(); ?>
                <button class="b-button b-button--link-feedback js-add-review">Оставить отзыв</button>
            </div>
        </div>
        <form class="b-form-review js-form-review js-form-validation js-review-query"
              novalidate id="commentsFormCatalog" data-url="/ajax/comments/catalog/add/" method="post">
            <input type="hidden" name="UF_TYPE" value="<?= $arParams['TYPE'] ?>" class="js-no-valid">
            <input type="hidden" name="HL_ID" value="<?= $arParams['HL_ID'] ?>" class="js-no-valid">
            <input type="hidden" name="UF_OBJECT_ID" value="<?= $arParams['OBJECT_ID'] ?>" class="js-no-valid">
            <input type="hidden" name="action" value="add" class="js-no-valid">
            <?php /** @noinspection PhpUnhandledExceptionInspection */
            $frame = $this->createFrame()->begin(''); ?>
            <div class="b-form-review__wrapper-blocks js-comments-auth-form-<?= $uniqueCommentString ?>"
                 style="display: none">
                <p class="b-form-review__text-block b-form-review__text-block--account">Укажите телефон или
                    почту, а
                    также пароль, если у
                    вас
                    есть аккаунт на
                    нашем
                    сайте</p>
                <div class="b-form-review__group">
                    <label class="b-form-review__label" for="id-review-tel">Мобильный телефон</label>
                    <input class="b-form-review__input js-phone-mask" id="id-review-tel" type="tel" name="PHONE" value=""/>
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
                <div class="b-form-review__group">
                    <label class="b-form-review__label" for="id-review-mail">Эл. почта</label>
                    <input class="b-form-review__input" id="id-review-mail" type="email" name="EMAIL" value=""/>
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
                <div class="b-form-review__group">
                    <label class="b-form-review__label" for="id-review-pass">Пароль</label>
                    <input class="b-form-review__input" id="id-review-pass" type="password" name="PASSWORD" value="" autocomplete="off" />
                    <div class="b-error"><span class="js-message"></span>
                    </div>
                </div>
            </div>
            <?php /** @noinspection PhpUnhandledExceptionInspection */
            $frame->end(); ?>
            <div class="b-form-review__wrapper-blocks">
                <div class="b-form-review__sub-heading">Оценка</div>
                <div class="b-rating b-rating--large b-rating--form-review">
                    <div class="b-rating__form">
                        <div class="b-rating__group">
                            <?php for ($i = 5; $i >= 1; $i--) { ?>
                                <input class="b-rating__input"
                                       type="radio"
                                       id="radio<?= $i ?>"
                                       name="UF_MARK"
                                       value="<?= $i ?>"/>
                                <label class="b-rating__star" for="radio<?= $i ?>">
                                    <span class="b-icon">
                                        <?= new SvgDecorator('icon-star-stroke', 13, 12) ?>
                                    </span>
                                </label>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="b-form-review__sub-heading">Отзыв</div>
                <div class="b-form-review__group">
                    <textarea class="b-form-review__textarea js-small-input-eight"
                              name="UF_TEXT"
                              placeholder="Оставьте ваш отзыв:"
                              maxlength="1000"></textarea>
                    <div class="b-error"><span class="js-message"></span></div>
                </div>
                <div class="js-comments-captcha-block-<?= $uniqueCommentString ?>" style="display: none"></div>
                <button class="b-button b-button--form-review" type="submit">Отправить</button>
            </div>
            <div class="b-form-review__wrapper-blocks js-success-review" style="display: none;">
                <p class="b-form-review__text-block js-text-review" id="commentStatus">
                    Ваш комментарий успешно отправлен, он появится здесь после проверки
                </p>
            </div>
        </form>
        <?php /** @noinspection PhpUnhandledExceptionInspection */
        $frame = $this->createFrame()->begin('');
        if (is_array($arResult['COMMENTS']) && $arResult['COUNT_COMMENTS'] > 0) { ?>
            <div class="b-review">
                <h2 class="b-review__heading">Отзывы</h2>
                <ul class="b-review__list">
                    <?php foreach ($arResult['COMMENTS'] as $comment) {?>
                        <li class="b-review__item"
                            itemprop="review"
                            itemscope
                            itemtype="http://schema.org/Review" >
                            <header class="b-review__left-side">
                                <p class="b-review__name" itemprop="author" ><?= $comment['USER_NAME'] ?></p>
                                <?php if(!empty($comment['DATE_FORMATED'])){ ?>
                                    <p class="b-review__date" itemprop="datePublished" ><?= $comment['DATE_FORMATED'] ?></p>
                                <?php } ?>
                            </header>
                            <div class="b-review__right-side">
                                <div class="b-rating b-rating--big">
                                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                                        <div class="b-rating__star-block<?= $comment['UF_MARK']
                                        >= $i ? ' b-rating__star-block--active' : '' ?>">
                                                    <span class="b-icon"><?= new SvgDecorator(
                                                            'icon-star', 12, 12
                                                        ) ?></span>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="b-review__text" itemprop="description" >
                                    <p><?= $comment['UF_TEXT'] ?></p>
                                </div>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
                <?php if ($arResult['COUNT_COMMENTS'] > count($arResult['COMMENTS'])) { ?>
                    <button class="b-button b-button--review js-add_review"
                            id="getNextCommentsBtn"
                            data-url="/ajax/comments/next/"
                            data-action="get"
                            data-hl_id="<?= $arParams['HL_ID'] ?>"
                            data-object_id="<?= $arParams['OBJECT_ID'] ?>"
                            data-type="<?= $arParams['TYPE'] ?>"
                            data-items_count="<?= $arParams['ITEMS_COUNT'] ?>"
                            data-page="1"
                            data-sort_desc="<?= $arParams['SORT_DESC'] ?>"
                            data-active_date_format="<?= $arParams['ACTIVE_DATE_FORMAT'] ?>">
                        Ещё отзывы
                    </button>
                <?php } ?>
            </div>
        <?php }
        /** @noinspection PhpUnhandledExceptionInspection */
        $frame->end(); ?>
    </div>
</div>
