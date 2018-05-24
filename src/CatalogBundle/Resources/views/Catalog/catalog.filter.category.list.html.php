<?php

/**
 * @var Request                               $request
 * @var CatalogCategorySearchRequestInterface $catalogRequest
 * @var ProductSearchResult                   $productSearchResult
 * @var PhpEngine                             $view
 * @var Category                              $category
 * @var CMain                                 $APPLICATION
 * @var bool                                  $isBrand
 * @var ArrayCollection                       $sectionIds
 */

//use FourPaws\Catalog\Collection\CategoryCollection;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\CatalogBundle\Dto\CatalogCategorySearchRequestInterface;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\PhpEngine;

/** @var Category $child */
if ($isBrand && !empty($brand)) {
    $instance = \Bitrix\Main\Application::getInstance();
    $requestSections = implode(',',$instance->getContext()->getRequest()->get('Sections'));
    /** @todo сброс кеша по тегу сделать */
    $childs = null;
    $cache = $instance->getCache();
    $cacheTime = 60 * 60;
    if ($cache->initCache($cacheTime,
        serialize(['brand' => $brand]))) {
        $result = $cache->getVars();
        $childs = $result['childs'];
    } elseif ($cache->startDataCache()) {
        $tagCache = new TaggedCacheHelper($cachePath);
        $products = (new \FourPaws\Catalog\Query\ProductQuery())->withFilter(['PROPERTY_BRAND.NAME' => $brand])->exec();
        /** @var \FourPaws\Catalog\Model\Product $product */
        $sectionIds = [];
        foreach ($products as $product) {
            $sectList = $product->getSectionsIdList();
            if (!empty($sectList)) {
                foreach ($sectList as $value) {
                    $sectionIds[] = $value;
                }
            }
        }
        if (!empty($sectionIds)) {
            $sectionIds = array_unique($sectionIds);
            $childs = (new CategoryQuery())->withFilter(['=ID' => $sectionIds])->exec();
            /** @var Category $section */
            foreach ($childs as $key => $section) {
                if ($section->getDepthLevel() > 1) {
                    $item = (new CategoryQuery())->withFilter(['>LEFT_MARGIN'  => $section->getLeftMargin(),
                                                               '<RIGHT_MARGIN' => $section->getRightMargin(),
                                                               'DEPTH_LEVEL'    => 1,
                    ])->withNav(['nTopCount'=>1])->exec()->first();
                    if($item instanceof Category) {
                        $childs->add($item);
                    }
                    $childs->remove($key);
                }
            }
        }

        $tagCache->addTag('catalog:brand:' . $brand);

        $tagCache->end();
        $cache->endDataCache(['childs' => $childs]);
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
                        <?php if ($isBrand) { ?>
                            <label class="b-filter-link-list__label">
                                <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                       type="checkbox"
                                       name="Sections"
                                       value="<?= $child->getId() ?>"
                                       id="Sections-<?= $child->getId() ?>"
                                    <?= \in_array($child->getId(), $requestSections) ? 'checked' : '' ?>
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
