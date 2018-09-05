<?php

namespace FourPaws\Components;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\MainTemplate;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Product;
use FourPaws\CatalogBundle\Service\CatalogLandingService;
use FourPaws\Helpers\TaggedCacheHelper;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsBreadCrumbs extends FourPawsComponent
{
    /**
     * @param $params
     * @return array
     * @throws SystemException
     */
    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000000;
        }

        $params['SHOW_LINK_TO_MAIN'] = ($params['SHOW_LINK_TO_MAIN'] === BitrixUtils::BX_BOOL_FALSE)
            ? BitrixUtils::BX_BOOL_FALSE
            : BitrixUtils::BX_BOOL_TRUE;
        $params['SHOW_CURRENT'] = $params['SHOW_CURRENT'] ?? false;
        $params['IBLOCK_SECTION'] = $params['IBLOCK_SECTION'] instanceof Category ? $params['IBLOCK_SECTION'] : null;
        $params['IBLOCK_ELEMENT'] = $params['IBLOCK_ELEMENT'] instanceof Product ? $params['IBLOCK_ELEMENT'] : null;

        /**
         * @var MainTemplate $template
         */
        $template = MainTemplate::getInstance(Application::getInstance()->getContext());
        $params['IS_CATALOG'] = $template->isCatalog();
        $params['IS_LANDING'] = CatalogLandingService::isLandingPage();

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @throws SystemException
     * @throws ApplicationCreateException
     */
    public function prepareResult(): void
    {
        if ($this->arParams['IS_LANDING']) {
            $this->arResult['BACK_LINK'] = CatalogLandingService::getBackLink();
        }

        if ($this->arParams['IBLOCK_ELEMENT']) {
            /** @var Product $product */
            $product = $this->arParams['IBLOCK_ELEMENT'];
            $this->arParams['IBLOCK_SECTION'] = $product->getSection();
        }

        if (null === $this->arParams['IBLOCK_SECTION']) {
            throw new \InvalidArgumentException('Invalid component parameters');
        }

        /** @var Category $category */
        $category = $this->arParams['IBLOCK_SECTION'];
        $parentSections = [];
        /** @var Category $parent */
        foreach ($category->getFullPathCollection() as $parent) {
            if (!$this->arParams['SHOW_CURRENT'] && $parent->getId() === $category->getId()) {
                continue;
            }

            $parentSections[] = [
                'ID'   => $parent->getId(),
                'CODE' => $parent->getCode(),
                'URL'  => $parent->getSectionPageUrl(),
                'NAME' => $parent->getName(),
            ];
        }
        $this->arResult['SECTIONS'] = \array_reverse($parentSections);

        if ($this->arParams['IS_LANDING']) {
            $this->arResult['BACK_LINK'] = CatalogLandingService::getBackLink();
        }
    }
}
