<?php

namespace FourPaws\Search\Model;

use CDBResult;
use Elastica\Result;
use Elastica\ResultSet;
use FourPaws\App\Application;
use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Search\Factory;

class ProductSearchResult
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
     * @var Factory
     */
    private $factory;

    /**
     * ProductSearchResult constructor.
     *
     * @param ResultSet $resultSet
     *
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function __construct(ResultSet $resultSet)
    {
        $this->resultSet = $resultSet;
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
            $this->productCollection = new ProductCollection($cdbres);
        }

        return $this->productCollection;
    }

    /**
     * @return ResultSet
     */
    public function getResultSet(): ResultSet
    {
        return $this->resultSet;
    }
}
