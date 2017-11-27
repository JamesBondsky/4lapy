<?php

namespace FourPaws\Migrator\Provider;

use FourPaws\Migrator\Converter\Decode;
use FourPaws\Migrator\Converter\DetailToProduct;
use FourPaws\Migrator\Converter\File;
use FourPaws\Migrator\Converter\StringToReference;

/**
 * Class Article
 *
 * @package FourPaws\Migrator\Provider
 */
class Article extends IBlockElement
{
    /**
     * @inheritdoc
     */
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
                               'DETAIL_PAGE_URL'      => 'PROPERTY_OLD_URL',
                               'PROPERTY_VIDEO'      => 'PROPERTY_VIDEO',
                           ]);
        
        return $map;
    }
    
    /**
     * Конвертеры для статей:
     *
     * - тип - загоняем в справочник
     * - артикулы (XML_ID, на самом деле) продуктов извлекаем из детального описания и добавляем в отдельное свойство
     *
     * @throws \Bitrix\Main\LoaderException
     *
     * @return array
     */
    public function getConverters() : array
    {
        $typeConverter              = new StringToReference('PROPERTY_TYPE');
        $detailTextConverter        = new DetailToProduct('DETAIL_TEXT');
        $detailTextDecodeConverter  = new Decode('DETAIL_TEXT');
        $previewTextDecodeConverter = new Decode('PREVIEW_TEXT');
        $detailPictureConverter     = new File('DETAIL_PICTURE');
        $previewPictureConverter    = new File('PREVIEW_PICTURE');
        /**
         * @todo плохо! Завязать на проект.
         */
        $typeConverter->setReferenceCode('ForWho');
        $detailTextConverter->setProductFieldName('PROPERTY_PRODUCTS');
        
        return [
            $typeConverter,
            $detailTextConverter,
            $detailPictureConverter,
            $previewPictureConverter,
            $detailTextDecodeConverter,
            $previewTextDecodeConverter,
        ];
    }
}
