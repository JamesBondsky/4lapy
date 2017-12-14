<?php

namespace FourPaws\Search\Model;

use CDBResult;
use Elastica\Result;
use Elastica\ResultSet;
use FourPaws\App\Application;
use FourPaws\Catalog\Collection\ProductCollection;

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
     * @return ProductCollection
     * @throws \RuntimeException
     */
    public function getProductCollection()
    {
        if (is_null($this->productCollection)) {

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
