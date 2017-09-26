<?php

namespace FourPaws\Migrator\Provider;

use FourPaws\Migrator\Converter\File;

/**
 * Class Catalog
 *
 * @package FourPaws\Migrator\Provider
 */
class Catalog extends IBlockElement
{
    public function getMap() : array
    {
        $map = parent::getMap();

        $map = array_merge($map,
                           [
                               /**
                                * @todo здесь будет бооооольшой маппинг
                                */
                               'PROPERTY_DESCRIPTION_CARD' => 'DETAIL_TEXT',
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
        $pictureConverter = new File('PROPERTY_IMG');

        /**
         * @todo здесь будет мнооооого конвертеров
         */
        return [
            $pictureConverter,
        ];
    }
}