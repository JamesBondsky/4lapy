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
            $explode     = explode('T', $data);
            $explodeDate = explode('-', $explode[0]);
            $explodeTime = explode(':', $explode[1]);
            if (!empty($explodeTime[2])) {
                $explodeMicroTime = explode('.', $explodeTime[2]);
            }
            $data = new \DateTimeImmutable();
            $data->setDate($explodeDate[0], $explodeDate[1], $explodeDate[2]);
            $data->setTime($explodeTime[0], $explodeTime[1], $explodeMicroTime[0] ?? 0, $explodeMicroTime[1] ?? 0);
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
            $explode     = explode('T', $data);
            $explodeDate = explode('-', $explode[0]);
            $explodeTime = explode(':', $explode[1]);

            if (!empty($explodeTime[2])) {
                $explodeMicroTime = explode('.', $explodeTime[2]);
            }
            $data = new \DateTimeImmutable();
            $data->setDate($explodeDate[0], $explodeDate[1], $explodeDate[2]);
            $data->setTime($explodeTime[0], $explodeTime[1], $explodeMicroTime[0] ?? 0, $explodeMicroTime[1] ?? 0);
        }
        
        return $data;
    }
}
