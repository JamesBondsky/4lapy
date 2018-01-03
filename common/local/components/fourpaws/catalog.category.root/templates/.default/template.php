<?php
/**
 * @var CBitrixComponentTemplate $this
 * @var array $arParams
 * @var array $arResult
 * @var Category $category
 * @var Category $childCategory
 * @var Category[] $childCategories
 * @var Category $childChildCategory
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
$categoriesWithChildren = $category->getChild()->filter(
    function (Category $category) {
        return $category->getChild()->count() > 0;
    }
);

?>
<?php $this->setViewTarget(ViewsEnum::CATALOG_CATEGORY_ROOT_LEFT_BLOCK) ?>
    <aside class="b-filter b-filter--accordion">
        <div class="b-filter__wrapper">
            <?php /** @var Category $cat */ ?>
            <?php foreach ($categoriesWithChildren as $cat) { ?>
                <div class="b-accordion b-accordion--filter">
                    <a class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                       href="javascript:void(0);"
                       title="<?= $cat->getName() ?>">
                        <?= $cat->getName() ?>
                    </a>
                    <div class="b-accordion__block js-dropdown-block">
                        <ul class="b-filter-link-list">
                            <?php foreach ($cat->getChild() as $child) { ?>
                                <li class="b-filter-link-list__item">
                                    <a class="b-filter-link-list__link"
                                       href="<?= $child->getSectionPageUrl() ?>"
                                       title="<?= $child->getName() ?>">
                                        <?= $child->getName() ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            <?php } ?>
        </div>
    </aside>
<?php $this->EndViewTarget() ?>

<?php $this->SetViewTarget(ViewsEnum::CATALOG_CATEGORY_ROOT_MAIN_BLOCK) ?>
    <main class="b-catalog__main b-catalog__main--first-step" role="main">
        <?php
        $childCategories = $categoriesWithChildren->slice(0, 2);
        include 'category_view.php';
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
        $childCategories = $categoriesWithChildren->slice(2);
        include 'category_view.php';
        ?>
    </main>
<?php $this->EndViewTarget();
