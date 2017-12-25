<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Main\LoaderException;
use FourPaws\Migrator\Converter\Decode;
use FourPaws\Migrator\Converter\File;
use FourPaws\Migrator\Converter\StringToBool;
use FourPaws\Migrator\Converter\StringToReference;

/**
 * Class Action
 *
 * @package FourPaws\Migrator\Provider
 */
class Action extends IBlockElement
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
                               'PROPERTY_type_animal' => 'PROPERTY_TYPE',
                               'PROPERTY_TYPE'        => 'PROPERTY_SHARE_TYPE',
                               'PROPERTY_ONLY_MP'     => 'PROPERTY_ONLY_MP',
                               'PROPERTY_URL'         => 'PROPERTY_SHORT_URL',
                               'DETAIL_PAGE_URL'      => 'PROPERTY_OLD_URL',
                           ]);
        
        return $map;
    }
    
    /**
     * Конвертеры для новостей:
     *
     * - тип - загоняем в справочник
     * - артикулы (XML_ID, на самом деле) продуктов извлекаем из детального описания и добавляем в отдельное свойство
     *
     * @throws LoaderException
     *
     * @return array
     */
    public function getConverters() : array
    {
        $typeConverter              = new StringToReference('PROPERTY_TYPE');
        $shareTypeConverter         = new StringToReference('PROPERTY_SHARE_TYPE');
        $onlyMpConverter            = new StringToBool('PROPERTY_ONLY_MP');
        $detailTextDecodeConverter  = new Decode('DETAIL_TEXT');
        $previewTextDecodeConverter = new Decode('PREVIEW_TEXT');
        $previewPictureConverter    = new File('PREVIEW_PICTURE');
        $detailPictureConverter     = new File('DETAIL_PICTURE');
        
        try {
            $typeConverter->setReferenceCode('PublicationType');
            $shareTypeConverter->setReferenceCode('ShareType');
        } catch (\Exception $e) {
        }
        
        return [
            $detailPictureConverter,
            $detailTextDecodeConverter,
            $onlyMpConverter,
            $previewPictureConverter,
            $previewTextDecodeConverter,
            $typeConverter,
            $shareTypeConverter,
        ];
    }
}
