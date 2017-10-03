<?php

namespace FourPaws\Migrator\Converter;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Highloadblock\DataManager;

/**
 * Class DetailToProduct
 *
 * !!! специфичный для проекта конвертер
 *
 * Убирает из детального описания артикулы товаров, сохраняя их в свойство.
 *
 * @package FourPaws\Migrator\Converter
 */
final class DetailToProduct extends AbstractConverter
{
    const PRODUCT_TEMPLATE = '~(?>\s*#t_id=(\d+)#\s*)~';
    
    private $productFieldName = '';
    
    /**
     * @return string
     */
    public function getProductFieldName() : string
    {
        return $this->productFieldName;
    }
    
    /**
     * @param string $productFieldName
     */
    public function setProductFieldName(string $productFieldName)
    {
        $this->productFieldName = $productFieldName;
    }
    
    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function convert(array $data) : array
    {
        if (!($productFieldName = $this->getProductFieldName())) {
            /**
             * @todo придумать нормальный Exception
             */
            throw new \Exception('Empty product field name');
        }
        
        $fieldName = $this->getFieldName();
        
        if (!$data[$fieldName]) {
            return $data;
        }
        
        if ($products = $this->getProducts($data[$fieldName])) {
            $data[$fieldName]        = $this->removeProducts($data[$fieldName]);
            $data[$productFieldName] = $products;
        }

        return $data;
    }
    
    /**
     * @param string $value
     *
     * @return array
     */
    public function getProducts(string $value) : array
    {
        preg_match_all(self::PRODUCT_TEMPLATE, $value, $products);
        
        return is_array($products[1]) ? $products[1] : [];
    }
    
    /**
     * @param string $value
     *
     * @return string
     */
    public function removeProducts(string $value) : string
    {
        return preg_replace(self::PRODUCT_TEMPLATE, '', $value);
    }
}