<?php

namespace FourPaws\Components;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Iblock\InheritedProperty\SectionValues;
use CBitrixComponent;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Category;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use WebArch\BitrixCache\BitrixCache;

CBitrixComponent::includeComponentClass('fourpaws:catalog.category');

/** @noinspection AutoloadingIssuesInspection */
class CatalogCategoryRoot extends CatalogCategory
{
    /**
     *
     * @throws IblockNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    protected function prepareResult(): void
    {
        if ($this->arParams['SECTION_CODE'] === '/') {
            $this->setRootCategory();
            $this->setTitle(null);

            return;
        }

        parent::prepareResult();

        /** @var Category $category */
        $category = $this->arResult['CATEGORY'];
        $this->setTitle($category);

        if (!$category->getChild()->count()) {
            $this->abortResultCache();
            Tools::process404('', true, true, true);
        }
    }

    /**
     * @throws IblockNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function setRootCategory()
    {
        $this->arResult['CATEGORY'] = Category::createRoot(['NAME' => 'Каталог']);
    }

    /**
     * @param Category|null $category
     * @throws \Exception
     */
    public function setTitle(?Category $category)
    {
        global $APPLICATION;
        if ($category == null) {
            if ($this->arParams['SET_TITLE'] === 'Y' && $APPLICATION->GetCurPage() != '/catalog/') {
                /**
                 * @var Category $catalog
                 */
                $catalog = $this->arResult['CATEGORY'];

                $APPLICATION->SetTitle($catalog->getCanonicalName());
            }
        } else {
            if ($this->arParams['SET_TITLE'] === 'Y' && $APPLICATION->GetCurPage() != '/catalog/') {
                /**
                 * @var Category $catalog
                 */
                $catalog = $this->arResult['CATEGORY'];

                $cache = (new BitrixCache())
                    ->withId(__METHOD__ . $category->getId())
                    ->withTime(3600);

                $properties = $cache->resultOf(function () use ($category) {
                    return \array_map(function ($meta) use ($category) {
                        try {
                            $minPrice = $this->arParams['MIN_PRICE_PRODUCT']->getOffersSorted()->first()->getCatalogPrice();
                        } catch (\Throwable $e) {
                            $minPrice = 0;
                        }

                        return \str_replace(
                            [
                                '#MIN_PRICE#',
                                '#PRODUCT_COUNT#',
                                '#PET_TYPE#'
                            ],
                            [
                                $minPrice,
                                $this->arParams['PRODUCT_COUNT'],
                                $this->getSuffix($category)
                            ],
                            $meta
                        );
                    }, (new SectionValues($category->getIblockId(), $category->getId()))->getValues());
                });

                if($properties['SECTION_PAGE_TITLE'] == null || $properties['SECTION_PAGE_TITLE'] == ''){
                    $APPLICATION->SetTitle($catalog->getCanonicalName());
                } else {
                    $APPLICATION->SetTitle($properties['SECTION_PAGE_TITLE']);
                }
            }
        }
    }
}
