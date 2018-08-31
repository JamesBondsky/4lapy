<?php use FourPaws\BitrixOrm\Collection\ArticleCollection;
use FourPaws\BitrixOrm\Model\Article;
use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array             $arParams
 * @var array             $arResult
 * @var ArticleCollection $articles
 * @var Article           $article
 */
$articles = $arResult['ARTICLES_COLLECTION'];

if (!$articles->count()) {
    return;
} ?>
<div class="to_note__wrapper">
    <div class="content_dropdown mobile_mq js-content-dropdown-trigger">
        <div class="content_dropdown__title">На заметку
            <div class="content_dropdown__arrow">
                <?= new SvgDecorator('icon-up-arrow'); ?>
            </div>
        </div>
    </div>
    <div class="content_dropdown__content unpadded js-content-dropdown-content">
        <div class="to_note">
            <div class="to_note__title tablet_up_mq">На заметку</div>
            <div class="to_note_article__wrapper">
                <?php foreach ($articles as $article) { ?>
                    <div class="to_note_article js-article-container">
                        <div class="to_note_article__image js-article-image"></div>
                        <div class="to_note_article__text_content">
                            <div class="to_note_article__title js-article-title"><?= $article->getName() ?></div>
                            <div class="to_note_article__preview_text"><?= $article->getPreviewText()
                                    ->getText() ?></div>
                            <div class="to_note_article__full_text js-article-text"><?= $article->getDetailText()
                                    ->getText() ?></div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
