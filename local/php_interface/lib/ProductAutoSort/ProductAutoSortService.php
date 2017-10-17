<?php

namespace FourPaws\ProductAutoSort;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\Catalog\Model\Product;
use FourPaws\ProductAutoSort\Helper\ValueHelper;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class ProductAutoSortService implements LoggerAwareInterface
{

    use LoggerAwareTrait;

    protected $valueHelper;

    public function __construct()
    {
        $this->setLogger(LoggerFactory::create('ProductAutoSortService'));
    }

    /**
     * Определяет, к каким категориям подходит товар и возвращает список id этих категорий.
     *
     * @param Product $product
     *
     * @return int[]
     */
    public function defineProductCategories(Product $product)
    {


        return [];
    }

    public function defineProductCategoriesMulti(array $idList)
    {

    }
    
    /**
     * Синхронизирует значение для условия свойства элемента
     *
     * @param int $ufId
     * @param int $sectionId
     * @param int $propertyId
     * @param mixed $value
     */
    public function syncValue(int $ufId, int $sectionId, int $propertyId, $value)
    {
        $this->getValueHelper()->syncValue($ufId, $sectionId, $propertyId, $value);
    }

    /**
     * Синхронизирует множество значений для условия свойства элемента
     *
     * @param int $ufId
     * @param int $sectionId
     * @param array $valueList
     */
    public function syncValueMulti(int $ufId, int $sectionId, array $valueList)
    {
        $this->getValueHelper()->syncValueMulti($ufId, $sectionId, $valueList);
    }

    /**
     * Удалить все значения для категории.
     *
     * @param int $sectionId
     */
    public function deleteValue(int $sectionId)
    {
        $this->getValueHelper()->deleteValue($sectionId);
    }

    public function getValueHelper()
    {
        if (is_null($this->valueHelper)) {
            $this->valueHelper = new ValueHelper();
        }

        return $this->valueHelper;
    }

    /**
     * @return LoggerInterface
     */
    public function log()
    {
        return $this->logger;
    }

}
