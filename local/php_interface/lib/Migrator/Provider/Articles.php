<?php

namespace FourPaws\Migrator\Provider;

use FourPaws\Migrator\Converter\StringToReference;

class Articles extends IblockProvider
{
    public function getMap() : array
    {
        $map = parent::getMap();
        
        /**
         * На PROPERTY_PRODUCTS не существует отображения, его мы собираем из детального описания,
         * прогоняя через конвертер
         */
        $map = array_merge($map,
                           [
                               'PROPERTY_animal_type' => 'PROPERTY_TYPE',
                               'PROPERTY_PRODUCTS'    => 'PROPERTY_PRODUCTS',
                           ]);
        
        return $map;
    }
    
    /**
     * Конвертеры для статей:
     *
     * - тип - загоняем в справочник
     * - артикулы (XML_ID, на самом деле) продуктов извлекаем из детального описания и добавляем в отдельное свойство
     *
     * @return array
     */
    public function getConverters() : array
    {
        $type = new StringToReference('PROPERTY_TYPE');
        $detailTextConverter = new DetailToProduct('DETAIL_TEXT');
        /**
         * @todo плохо! Завязать на проект.
         */
        $type->setReferenceCode('ForWho');
        $detailTextConverter->setProductFieldName('PROPERTY_PRODUCTS');
    
        return [
            $typeConverter,
            $detailTextConverter,
        ];
    }
}