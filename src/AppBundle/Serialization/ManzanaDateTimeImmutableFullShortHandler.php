<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Serialization;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
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
                'method'    => 'deserializeXml',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'xml',
                'type'      => 'manzana_date_time_short',
                'method'    => 'serializeXml',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
                'type'      => 'manzana_date_time_short',
                'method'    => 'deserializeJson',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'manzana_date_time_short',
                'method'    => 'serializeJson',
            ],
        ];
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param XmlSerializationVisitor                  $visitor
     * @param                                          $data
     * @param array                                    $type
     * @param Context                                  $context
     *
     * @return mixed
     */
    public function serializeXml(XmlSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        /** format Y-m-d\TH:i:s.u */
        if (!empty($data) && !($data instanceof \DateTimeImmutable)) {
            $data = new \DateTimeImmutable($data);
        }
        
        return $data->format('Y-m-d\TH:i:s.u');
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param XmlDeSerializationVisitor                    $visitor
     * @param                                            $data
     * @param array                                      $type
     * @param Context                                    $context
     *
     * @return mixed
     */
    public function deserializeXml(XmlDeSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        /** format Y-m-d\TH:i:s.u */
        if (!empty($data) && !($data instanceof \DateTimeImmutable)) {
            $data = new \DateTimeImmutable($data);
        }
        
        return $data;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param JsonSerializationVisitor                  $visitor
     * @param                                          $data
     * @param array                                    $type
     * @param Context                                  $context
     *
     * @return mixed
     */
    public function serializeJson(JsonSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        /** format Y-m-d\TH:i:s.u */
        if (!empty($data) && !($data instanceof \DateTimeImmutable)) {
            $data = new \DateTimeImmutable($data);
        }

        return $data->format('Y-m-d\TH:i:s.u');
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param JsonDeserializationVisitor                    $visitor
     * @param                                            $data
     * @param array                                      $type
     * @param Context                                    $context
     *
     * @return mixed
     */
    public function deserializeJson(JsonDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        /** format Y-m-d\TH:i:s.u */
        if (!empty($data) && !($data instanceof \DateTimeImmutable)) {
            $data = new \DateTimeImmutable($data);
        }

        return $data;
    }
}
