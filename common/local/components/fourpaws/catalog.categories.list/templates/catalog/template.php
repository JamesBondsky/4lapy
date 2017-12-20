<?php
/**
 * @var CBitrixComponentTemplate $this
 * @var array                    $arParams
 * @var array                    $arResult
 * @var Category                 $category
 * @var Category                 $childCategory
 * @var Category[]               $childCategories
 * @var Category                 $childChildCategory
 */

use FourPaws\App\Templates\ViewsEnum;
use FourPaws\Catalog\Model\Category;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$category = $arResult['CATEGORY'];


/**
 * Удаляем категории без детей
 * @todo Что делать с такими категориями?
 */
$child = $category->getChild()->filter(function (Category $category) {
    return 0 === $category->getChild()->count();
});

$this->SetViewTarget(ViewsEnum::CATALOG_CATEGORIES_LIST);

?>
    <main class="b-catalog__main b-catalog__main--first-step" role="main">
        <?php

        $childCategories = $category->getChild()->slice(0, 2);
        include __DIR__ . '/category_view.php';

        /**
         * @todo Баннер
         */
        ?>
        <div class="b-main-item b-main-item--catalog">
            <a class="b-main-item__link b-main-item__link--catalog" href="javascript:void(0);" title="" alt="">
                <img class="b-main-item__slider-background b-main-item__slider-background--desktop"
                     src="/static/build/images/content/banner.jpg" alt="" role="presentation"/>
                <img class="b-main-item__slider-background b-main-item__slider-background--min-desktop"
                     src="/static/build/images/content/banner.jpg" alt="" role="presentation"/>
                <img class="b-main-item__slider-background b-main-item__slider-background--tablet"
                     src="/static/build/images/content/banner.jpg" alt="" role="presentation"/>
                <img class="b-main-item__slider-background b-main-item__slider-background--mobile"
                     src="/static/build/images/content/banner.jpg" alt="" role="presentation"/>
            </a>
        </div>
        <?php

        $childCategories = $category->getChild()->slice(2);
        include __DIR__ . '/category_view.php';

        ?>
    </main>
<?php
$this->EndViewTarget();
