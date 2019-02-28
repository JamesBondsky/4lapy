<?php

namespace FourPaws\Components;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use CBitrixComponent;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use RuntimeException;

/** @noinspection AutoloadingIssuesInspection */
class CatalogSaleListComponent extends CBitrixComponent
{
    public const PROPERTY_SALE = 'PROPERTY_SALE';

    /**
     * @var GoogleEcommerceService
     */
    protected $ecommerceService;
    protected $filter;

    /**
     * CatalogSaleListComponent constructor.
     *
     * @param $component
     *
     * @throws ApplicationCreateException
     */
    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->ecommerceService = Application::getInstance()->getContainer()->get(GoogleEcommerceService::class);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 36000;
        $params['SECTION_CODE'] = $params['SECTION_CODE'] ?? '';
        $params['SECTION_CODE'] = $params['SECTION_CODE'] ?? '';
        $params['COUNT'] = (int)$params['COUNT'] ?: 12;
        $params['ECOMMERCE_LIST_NAME'] = (string)$params['ECOMMERCE_LIST_NAME'] ?: (string)$params['TITLE'];
        return parent::onPrepareComponentParams($params);
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     * @throws IblockNotFoundException
     */
    public function executeComponent()
    {
        if ($this->startResultCache()) {
            parent::executeComponent();

            $this->prepareResult();

            $this->includeComponentTemplate();
        }
    }

    /**
     * Set product collection
     *
     * @throws RuntimeException
     * @throws IblockNotFoundException
     */
    protected function prepareResult(): void
    {
        $this->prepareProductFilter();
        $this->arResult['products'] = $this->getProductList();
        $this->prepareEcommerce();
    }

    /**
     * Set ecommerce
     *
     * @throws RuntimeException
     */
    protected function prepareEcommerce(): void
    {
        $this->arResult['ECOMMERCE_VIEW_SCRIPT'] =
            $this->ecommerceService->renderScript(
                $this->ecommerceService->buildImpressionsFromProductCollection(
                    $this->arResult['products'], $this->arParams['ECOMMERCE_LIST_NAME']
                ), true
            );
    }

    /**
     * @throws IblockNotFoundException
     */
    protected function prepareProductFilter(): void
    {
        $this->filter = [];

        if ($this->arParams['PRODUCT_FILTER']) {
            $this->filter = $this->arParams['PRODUCT_FILTER'];
        }

        if ($this->arParams['OFFER_FILTER'] && \is_array($this->arParams['OFFER_FILTER'])) {
            $this->filter['ID'] = \CIBlockElement::SubQuery('PROPERTY_CML2_LINK',
                \array_merge($this->arParams['OFFER_FILTER'], [
                        'IBLOCK_ID' => IblockUtils::getIblockId(
                            IblockType::CATALOG,
                            IblockCode::OFFERS
                        ),
                    ]
                )
            );
        }

        $this->filter = $this->filter ?: ['ID' => '-1'];
    }

    /**
     * @return CollectionBase|ProductCollection
     */
    protected function getProductList(): ProductCollection
    {
        return (new ProductQuery())->withFilter($this->filter)->withOrder(['sort' => 'asc'])->withNav(['nTopCount' => $this->arParams['COUNT']])->exec();
    }

    /**
     * @return ProductCollection
     */
    public function getProductCollection(): ?ProductCollection
    {
        return $this->arResult['products'];
    }
}
