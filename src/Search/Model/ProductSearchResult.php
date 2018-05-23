<?php

namespace FourPaws\Search\Model;

use CDBResult;
use Elastica\Result;
use Elastica\ResultSet;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Search\Factory;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;

class ProductSearchResult implements ProductResultInterface
{
    /**
     * @var ProductCollection
     */
    protected $productCollection;

    /**
     * @var ResultSet
     */
    private $resultSet;

    /**
     * @var Navigation
     */
    private $navigation;

    /**
     * @var string
     */
    private $query;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * ProductSearchResult constructor.
     *
     * @param ResultSet  $resultSet
     * @param Navigation $navigation
     * @param string     $query
     *
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(ResultSet $resultSet, Navigation $navigation = null, $query = '')
    {
        $this->resultSet = $resultSet;
        $this->navigation = $navigation;
        $this->query = $query;
        $this->factory = Application::getInstance()->getContainer()->get('search.factory');
    }

    /**
     * @throws \RuntimeException
     * @return ProductCollection
     */
    public function getProductCollection(): ProductCollection
    {
        if (null === $this->productCollection) {
            $productList = [];

            /** @var Result $item */
            foreach ($this->resultSet as $item) {
                $productList[] = $this->factory->makeProductObject($item);
            }

            $cdbres = new CDBResult(null);
            $cdbres->InitFromArray($productList);

            if ($this->navigation) {
                $cdbres->NavRecordCount = $this->resultSet->getTotalHits();
                $cdbres->NavPageNomer = $this->navigation->getPage();
                $cdbres->NavPageSize = $this->navigation->getPageSize();
                $cdbres->NavPageCount = ceil($this->resultSet->getTotalHits() / $this->navigation->getPageSize());
            }

            $this->productCollection = new ProductCollection($cdbres);
        }

        return $this->productCollection;
    }

    public function getProductIds(): array
    {
        $productIds = [];

        /** @var Result $item */
        foreach ($this->resultSet as $item) {
            $productIds[] = (int)$item->getId();
        }

        return $productIds;
    }

    /**
     * @return ResultSet
     */
    public function getResultSet(): ResultSet
    {
        return $this->resultSet;
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}
