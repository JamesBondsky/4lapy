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

use FourPaws\Decorators\SvgDecorator;
$uniqueCommentString = $arParams['TYPE'] . '_' . $arParams['HL_ID'] . '_' . $arParams['OBJECT_ID']; ?>
<div class="b-container">
    <div class="b-comment-block">
        <p class="b-comment-block__title">Комментарии</p>
        <?php /** @noinspection PhpUnhandledExceptionInspection */
        $frame = $this->createFrame()->begin(); ?>
        <?php if ($arResult['COUNT_COMMENTS'] === 0) { ?>
            <div class="b-comment-block__info--block">
                <p class="b-comment-block__info">Пока никто не оставил комментарии.</p>
            </div>
        <?php } ?>
        <div class="b-comment-block__auth--block js-comments-auth-block-<?=$uniqueCommentString ?>" style="display: none">
            <p class="b-comment-block__auth">
                <a href="#">Авторизуйтесь</a> , чтобы написать комментарий.
            </p>
        </div>
        <?php /** @noinspection PhpUnhandledExceptionInspection */
        $frame->beginStub(); ?>
        <div class="b-comment-block__info--block">
            <p class="b-comment-block__info">Пока никто не оставил комментарии.</p>
        </div>
        <?php /** @noinspection PhpUnhandledExceptionInspection */
        $frame->end(); ?>
        <div class="tab-content-review">
            <div class="b-rate-block">
                <?php /** @noinspection PhpUnhandledExceptionInspection */
                $frame = $this->createFrame()->begin();
                if ($arResult['COUNT_COMMENTS'] > 0) { ?>
                    <div class="b-rate-block__left-side">
                        <p class="b-rate-block__name">Рейтинг</p>
                    </div>
                <?php }
                /** @noinspection PhpUnhandledExceptionInspection */
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
                                                                         отзывов</span>
                        </div>
                    <?php }
                    /** @noinspection PhpUnhandledExceptionInspection */
                    $frame->end(); ?>
                    <button class="b-button b-button--link-feedback js-add-review">Оставить отзыв</button>
                </div>
            </div>
            <form class="b-form-review js-form-review js-form-validation js-review-query"
                  id="commentsForm"
                  data-url="/ajax/comments/add/"
                  method="post">
                <input type="hidden" name="UF_TYPE" value="<?= $arParams['TYPE'] ?>" class="js-no-valid">
                <input type="hidden" name="HL_ID" value="<?= $arParams['HL_ID'] ?>" class="js-no-valid">
                <input type="hidden" name="UF_OBJECT_ID" value="<?= $arParams['OBJECT_ID'] ?>" class="js-no-valid">
                <input type="hidden" name="action" value="add" class="js-no-valid">
                <?php /** @noinspection PhpUnhandledExceptionInspection */
                $frame = $this->createFrame()->begin(''); ?>
                    <div class="b-form-review__wrapper-blocks js-comments-auth-form-<?=$uniqueCommentString ?>" style="display: none">
                        <p class="b-form-review__text-block b-form-review__text-block--account">Укажите телефон или
                                                                                                почту, а
                                                                                                также пароль, если у
                                                                                                вас
                                                                                                есть аккаунт на
                                                                                                нашем
                                                                                                сайте</p>
                        <div class="b-form-review__group">
                            <label class="b-form-review__label" for="id-review-tel">Мобильный телефон</label>
                            <input class="b-form-review__input js-phone-mask"
                                   id="id-review-tel"
                                   type="tel"
                                   name="PHONE"
                                   value="" />
                            <div class="b-error"><span class="js-message"></span>
                            </div>
                        </div>
                        <div class="b-form-review__group">
                            <label class="b-form-review__label" for="id-review-mail">Эл. почта</label>
                            <input class="b-form-review__input"
                                   id="id-review-mail"
                                   type="email"
                                   name="EMAIL"
                                   value="" />
                            <div class="b-error"><span class="js-message"></span>
                            </div>
                        </div>
                        <div class="b-form-review__group">
                            <label class="b-form-review__label" for="id-review-pass">Пароль</label>
                            <input class="b-form-review__input"
                                   id="id-review-pass"
                                   type="password"
                                   name="PASSWORD"
                                   value=""
                                   autocomplete="off" />
                            <div class="b-error"><span class="js-message"></span>
                            </div>
                        </div>
                    </div>
                <?php /** @noinspection PhpUnhandledExceptionInspection */
                $frame->end(); ?>
                <div class="b-form-review__wrapper-blocks">
                    <h4 class="b-form-review__sub-heading">Оценка</h4>
                    <div class="b-rating b-rating--large b-rating--form-review">
                        <div class="b-rating__form">
                            <div class="b-rating__group">
                                <?php for ($i = 5; $i >= 1; $i--) { ?>
                                    <input class="b-rating__input"
                                           type="radio"
                                           id="radio<?= $i ?>"
                                           name="UF_MARK"
                                           value="<?= $i ?>" />
                                    <label class="b-rating__star" for="radio<?= $i ?>">
                                    <span class="b-icon">
                                        <?= new SvgDecorator('icon-star-stroke', 13, 12) ?>
                                    </span>
                                    </label>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <h4 class="b-form-review__sub-heading">Отзыв</h4>
                    <div class="b-form-review__group">
                    <textarea class="b-form-review__textarea js-small-input-eight"
                              name="UF_TEXT"
                              required="required"
                              placeholder="Оставьте ваш отзыв:"
                              minlength="8"
                              maxlength="1000"></textarea>
                        <div class="b-error"><span class="js-message"></span></div>
                    </div>
                    <div class="js-comments-captcha-block-<?= $uniqueCommentString ?>" style="display: none"></div>
                    <button class="b-button b-button--form-review" type="submit">Отправить</button>
                </div>
                <div class="b-form-review__wrapper-blocks js-success-review">
                    <p class="b-form-review__text-block js-text-review" id="commentStatus"></p>
                </div>
            </form>
            <?php /** @noinspection PhpUnhandledExceptionInspection */
            $frame = $this->createFrame()->begin(''); ?>
            <?php if (is_array($arResult['COMMENTS']) && $arResult['COUNT_COMMENTS'] > 0) {
                ?>
                <div class="b-review">
                    <h2 class="b-review__heading">Отзывы</h2>
                    <ul class="b-review__list">
                        <?php foreach ($arResult['COMMENTS'] as $comment) {
                    ?>
                            <li class="b-review__item">
                                <header class="b-review__left-side">
                                    <p class="b-review__name"><?= $comment['USER_NAME'] ?></p>
                                    <p class="b-review__date"><?= $comment['DATE_FORMATED'] ?></p>
                                </header>
                                <div class="b-review__right-side">
                                    <div class="b-rating b-rating--big">
                                        <?php for ($i = 1; $i <= 5; $i++) {
                        ?>
                                            <div class="b-rating__star-block<?= $comment['UF_MARK']
                                                                                >= $i ? ' b-rating__star-block--active' : '' ?>">
                                                    <span class="b-icon"><?= new SvgDecorator(
                                                            'icon-star',
                                                                                    12,
                                                                                    12
                                                        ) ?></span>
                                            </div>
                                            <?php
                    } ?>
                                    </div>
                                    <div class="b-review__text">
                                        <p><?= $comment['UF_TEXT'] ?></p>
                                    </div>
                                </div>
                            </li>
                            <?php
                } ?>
                    </ul>
                    <?php if ($arResult['COUNT_COMMENTS'] > count($arResult['COMMENTS'])) {
                    ?>
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
                                data-active_date_format="<?= $arParams['ACTIVE_DATE_FORMAT'] ?>"
                        >
                            Ещё отзывы
                        </button>
                        <?php
                } ?>
                </div>
                <?php
            } ?>
            <?php /** @noinspection PhpUnhandledExceptionInspection */
            $frame->end(); ?>
        </div>
    </div>
</div>