<?php

namespace FourPaws\Search\Model;

use CDBResult;
use Elastica\ResultSet;
use FourPaws\App\Application;
use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Search\Factory;

class ProductSuggestResult implements ProductResultInterface
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

            $suggests = $this->resultSet->getSuggests();
            foreach ($suggests as $name => $data) {
                $items = $data[0]['options'];
                foreach ($items as $item) {
                    $productList[] = $this->factory->makeProductObjectFromArray($item['_source']);
                }
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
