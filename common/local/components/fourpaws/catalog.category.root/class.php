<?php

namespace FourPaws\Components;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Bitrix\Iblock\Component\Tools;
use CBitrixComponent;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Category;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

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
    protected function prepareResult()
    {
        if ($this->arParams['SECTION_CODE'] === '/') {
            $this->setRootCategory();
            $this->setTitle();

            return;
        }

        parent::prepareResult();

        /** @var Category $category */
        $category = $this->arResult['CATEGORY'];
        $this->setTitle();

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

    public function setTitle()
    {
        if ($this->arParams['SET_TITLE'] === 'Y') {
            global $APPLICATION;
            /**
             * @var Category $catalog
             */
            $catalog = $this->arResult['CATEGORY'];

            $APPLICATION->SetTitle($catalog->getCanonicalName());
        }
    }
}
