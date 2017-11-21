<?php

namespace FourPaws\Search\Model;

use CDBResult;
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

    //TODO Добавить аггрегации

    public function __construct(ResultSet $resultSet)
    {
        $this->resultSet = $resultSet;
        $this->factory = Application::getInstance()->getContainer()->get('search.factory');
    }

    public function getProductCollection()
    {
        if (is_null($this->productCollection)) {

            $productList = [];

            foreach ($this->resultSet as $item) {
                $productList[] = $this->factory->makeProductObject($item);
            }

            $cdbres = new CDBResult(null);
            $cdbres->InitFromArray($productList);
            $this->productCollection = new ProductCollection($cdbres);

        }

        return $this->productCollection;
    }
}
