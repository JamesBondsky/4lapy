<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Serialization;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;

class ManzanaDateTimeImmutableFullShortHandler implements SubscribingHandlerInterface
{
    /**
     * @return array
     */
    public static function getSubscribingMethods() : array
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'xml',
                'type'      => 'manzana_date_time_short',
                'method'    => 'deserialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'xml',
                'type'      => 'manzana_date_time_short',
                'method'    => 'serialize',
            ],
        ];
    }/** @noinspection MoreThanThreeArgumentsInspection */
    
    /**
     * @param XmlSerializationVisitor                  $visitor
     * @param                                          $data
     * @param array                                    $type
     * @param Context                                  $context
     *
     * @return mixed
     */
    public function serialize(XmlSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        /** format Y-m-d\TH:i:s.u */
        if (!empty($data) && !($data instanceof \DateTimeImmutable)) {
            $data = new \DateTimeImmutable($data);
        }
        
        return $data->format('Y-m-d\TH:i:s.u');
    }/** @noinspection MoreThanThreeArgumentsInspection */
    
    /**
     * @param XmlDeSerializationVisitor                    $visitor
     * @param                                            $data
     * @param array                                      $type
     * @param Context                                    $context
     *
     * @return mixed
     */
    public function deserialize(XmlDeSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        /** format Y-m-d\TH:i:s.u */
        if (!empty($data) && !($data instanceof \DateTimeImmutable)) {
            $data = new \DateTimeImmutable($data);
        }
        
        return $data;
    }
}
