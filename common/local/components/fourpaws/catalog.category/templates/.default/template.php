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
use FourPaws\Decorators\SvgDecorator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$category = $arResult['CATEGORY'];

?>
<?php $this->setViewTarget(ViewsEnum::CATALOG_CATEGORY_BACK_LINK) ?>
<?php
$parents = [];
$tmpCategory = $category;

do {
    $parent = $tmpCategory->getParent();
    $tmpCategory = $parent;
    if ($parent->getId()) {
        $parents[] = $parent;
    }
} while ($parent->getId());
$parents = array_reverse($parents);
?>
<?php if (!empty($parents)) { ?>
    <div class="b-filter__block b-filter__block--back">
        <ul class="b-back">
            <?php foreach ($parents as $parent) { ?>
                <li class="b-back__item">
                    <a class="b-link b-link--back" href="<?= $parent->getSectionPageUrl() ?>"
                       title="<?= $parent->getDisplayName() ?: $parent->getName() ?>">
                        <?= $parent->getDisplayName() ?: $parent->getName() ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>
<?php $this->EndViewTarget() ?>

<?php $this->SetViewTarget(ViewsEnum::CATALOG_CATEGORY_CHILDREN_LIST) ?>
<?php if ($category->getChild()->count()) { ?>
    <div class="b-filter__block b-filter__block--select">
        <h3 class="b-title b-title--filter-header">
            Категория
        </h3>
        <div class="b-select b-select--filter">
            <ul class="b-filter-link-list b-filter-link-list--filter b-filter-link-list--select-filter js-accordion-filter-select js-filter-checkbox">
                <?php /** @var Category $child */ ?>
                <?php foreach ($category->getChild() as $child) { ?>
                    <li class="b-filter-link-list__item">
                        <a class="b-filter-link-list__link"
                           href="<?= $child->getSectionPageUrl() ?>"
                           title="<?= $child->getDisplayName() ?: $child->getName() ?>">
                            <?= $child->getDisplayName() ?: $child->getName() ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <a class="b-link b-link--filter-more b-link--filter-select js-open-filter-all"
               href="javascript:void(0);" title="Показать все">
                Показать все
                <span class="b-icon b-icon--more">
                    <?= new SvgDecorator('icon-arrow-down', 10, 10) ?>
                </span>
            </a>
        </div>
    </div>
<?php } ?>
<?php $this->EndViewTarget();
