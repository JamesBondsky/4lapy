<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Sale\Delivery\Services\Configurable;
use Bitrix\Sale\Delivery\Services\Table;
use FourPaws\Migrator\Converter\File;

/**
 * Class Delivery
 *
 * @package FourPaws\Migrator\Provider
 */
class Delivery extends Sale
{
    /**
     * Пока умеем переносить только настраиваемые службы доставки
     */
    const DELIVERY_CLASS_NAME = Configurable::class;
    
    /**
     * @inheritdoc
     */
    public function getMap() : array
    {
        static $map;
        
        if (!$map) {
            $map = array_diff(array_keys(array_filter(Table::getMap(), $this->getScalarEntityMapFilter())),
                              [
                                  $this->entity->getPrimary(),
                                  'TRACKING_PARAMS',
                                  'ALLOW_EDIT_SHIPMENT',
                                  'PARENT_ID',
                                  'LID',
                              ]);
            
            $map = array_combine($map, $map);
        }
        
        return $map;
    }
    
    /**
     * @param array $data
     *
     * @return array
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \RuntimeException
     */
    public function prepareData(array $data) : array
    {
        $data = array_merge(parent::prepareData($data),
                            [
                                'ALLOW_EDIT_SHIPMENT' => 'N',
                                'ACTIVE'              => 'N',
                                'CLASS_NAME'          => self::DELIVERY_CLASS_NAME,
                            ]);
        
        return $data;
    }
    
    /**
     * @inheritdoc
     */
    public function getConverters() : array
    {
        $logoConverter = new File('PREVIEW_PICTURE');
        
        return [
            $logoConverter,
        ];
    }
}
