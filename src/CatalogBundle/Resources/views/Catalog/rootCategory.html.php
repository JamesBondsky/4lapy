<?php
/**
 * @var RootCategoryRequest $rootCategoryRequest
 * @var CMain $APPLICATION
 */

use FourPaws\App\Templates\ViewsEnum;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use FourPaws\Catalog\Model\Category;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

global $APPLICATION;

/**
 * @var Category $category
 */
$category = $APPLICATION->IncludeComponent(
    'fourpaws:catalog.category.root',
    '',
    [
        'SECTION_CODE' => $rootCategoryRequest->getCategorySlug(),
        'SET_TITLE' => 'Y',
        'CACHE_TIME' => 10
    ],
    $component,
    ['HIDE_ICONS' => 'Y']
);
?>
    <div class="b-catalog">
        <div class="b-container b-container--catalog-main">
            <div class="b-catalog__wrapper-title">
                <?php
                $APPLICATION->IncludeComponent(
                    'fourpaws:breadcrumbs',
                    '',
                    [
                        'IBLOCK_SECTION' => $category,
                    ],
                    $component,
                    ['HIDE_ICONS' => 'Y']
                );
                ?>
                <nav class="b-breadcrumbs b-breadcrumbs--catalog-main">
                    <ul class="b-breadcrumbs__list">
                        <li class="b-breadcrumbs__item">
                            <a class="b-breadcrumbs__link" href="javascript:void(0);"
                               title="Главная">Главная</a>
                        </li>
                    </ul>
                </nav>
                <h1 class="b-title b-title--h1"><?= $category->getCanonicalName() ?></h1>
            </div>
            <?php $APPLICATION->ShowViewContent(ViewsEnum::CATALOG_CATEGORY_ROOT_LEFT_BLOCK) ?>
            <?php $APPLICATION->ShowViewContent(ViewsEnum::CATALOG_CATEGORY_ROOT_MAIN_BLOCK) ?>
            <?php
            /**
             * @todo brands in section
             */
            ?>
            <div class="b-catalog__brand">
                <div class="b-line b-line--catalog"></div>
                <section class="b-common-section">
                    <div class="b-common-section__title-box b-common-section__title-box--catalog b-common-section__title-box--catalog-popular">
                        <h2 class="b-title b-title--catalog b-title--catalog-popular">Популярные бренды</h2>
                    </div>
                    <div class="b-common-section__content b-common-section__content--catalog b-common-section__content--catalog-popular">
                        <div class="b-popular-brand">
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog" title="Hill's"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/hills.jpg"
                                         alt="Hill's" title="Hill's"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog"
                                   title="Perfect fit" href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/perfect-fit.jpg" alt="Perfect fit"
                                         title="Perfect fit"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog" title="Purina"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/purina.jpg" alt="Purina" title="Purina"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog"
                                   title="Whiskas"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/whiskas.jpg" alt="Whiskas" title="Whiskas"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog" title="Felix"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/felix.jpg"
                                         alt="Felix" title="Felix"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog"
                                   title="Royal Canin" href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/royal-canin.jpg" alt="Royal Canin"
                                         title="Royal Canin"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog" title="Brit"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/brit.jpg"
                                         alt="Brit" title="Brit"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog" title="Bozita"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/bozita.jpg" alt="Bozita" title="Bozita"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog"
                                   title="Eukanuba"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/eukanuba.jpg" alt="Eukanuba"
                                         title="Eukanuba"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog" title="Acana"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/acana.jpg"
                                         alt="Acana" title="Acana"/>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <?php
        /**
         * Просмотренные товары
         */
        $APPLICATION->IncludeComponent(
            'bitrix:main.include',
            '',
            [
                'AREA_FILE_SHOW' => 'file',
                'PATH' => '/local/include/blocks/viewed_products.php',
                'EDIT_TEMPLATE' => '',
            ],
            null,
            [
                'HIDE_ICONS' => 'Y',
            ]
        );
    ?></div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
die();
