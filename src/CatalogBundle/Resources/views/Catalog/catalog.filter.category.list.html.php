<?php

/**
 * @var Request $request
 * @var CatalogCategorySearchRequestInterface $catalogRequest
 * @var ProductSearchResult $productSearchResult
 * @var PhpEngine $view
 * @var Category $category
 * @var CMain $APPLICATION
 * @var bool $isBrand
 * @var ArrayCollection $sectionIds
 */

//use FourPaws\Catalog\Collection\CategoryCollection;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\CatalogBundle\Dto\CatalogCategorySearchRequestInterface;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\PhpEngine;
/** @var Category $child */
if($isBrand && !$sectionIds->isEmpty()){
    $requestSections = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->get('Sections');
    $childs = (new CategoryQuery())->withFilter(['ID'=>array_unique($sectionIds->toArray())])->exec();
    foreach ($childs as $key => $child){
        if($child->getDepthLevel() > 1){
            $childs->remove($key);
        }
    }
} else {
    $childs = $category->getChild();
}
if ($childs !== null && $childs->count()) { ?>
    <div class="b-filter__block b-filter__block--select">
        <h3 class="b-title b-title--filter-header">
            Категория
        </h3>
        <div class="b-select b-select--filter">
            <ul class="b-filter-link-list b-filter-link-list--filter b-filter-link-list--select-filter js-accordion-filter-select js-filter-checkbox">
                <?php foreach ($childs as $child) { ?>
                    <li class="b-filter-link-list__item">
                        <?php if($isBrand){ ?>
                            <label class="b-filter-link-list__label">
                                <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                       type="checkbox"
                                       name="Sections[]"
                                       value="<?= $child->getId() ?>"
                                       id="Sections-<?= $child->getId() ?>"
                                    <?= \in_array($child->getId(), $requestSections)  ? 'checked' : '' ?>
                                />
                                <a class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                                   href="javascript:void(0);"
                                   title="<?= $child->getName() ?>"
                                ><?= $child->getName() ?></a>
                            </label>
                        <?php } else { ?>
                            <a class="b-filter-link-list__link"
                               href="<?= $child->getSectionPageUrl() ?>"
                               title="<?= $child->getCanonicalName() ?>">
                                <?= $child->getCanonicalName() ?>
                            </a>
                        <?php } ?>
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
