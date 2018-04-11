<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Serialization;

use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\DateTime;
use FourPaws\AppBundle\DeserializationVisitor\CsvDeserializationVisitor;
use FourPaws\AppBundle\SerializationVisitor\CsvSerializationVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;

/**
 * Class BitrixDateTimeHandler
 *
 * @package FourPaws\AppBundle\Serialization
 */
class BitrixDateTimeHandler implements SubscribingHandlerInterface
{
    /**
     * @return array
     */
    public static function getSubscribingMethods() : array
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
                'type'      => 'bitrix_date_time',
                'method'    => 'deserialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'xml',
                'type'      => 'bitrix_date_time',
                'method'    => 'deserializeXml',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'csv',
                'type'      => 'bitrix_date_time',
                'method'    => 'deserializeCsv',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'bitrix_date_time',
                'method'    => 'serialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'xml',
                'type'      => 'bitrix_date_time',
                'method'    => 'serializeXml',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'csv',
                'type'      => 'bitrix_date_time',
                'method'    => 'serializeCsv',
            ],
        ];
    }
    
    /**
     * @param JsonSerializationVisitor                 $visitor
     * @param                                          $data
     * @param array                                    $type
     * @param Context                                  $context
     *
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        if (!($data instanceof DateTime)) {
            $data = null;
        }
        
        return $data;
    }

    /**
     * формат d.m.Y H:i:s
     * @param XmlSerializationVisitor                 $visitor
     * @param                                          $data
     * @param array                                    $type
     * @param Context                                  $context
     *
     * @return mixed
     */
    public function serializeXml(XmlSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        if (!($data instanceof DateTime)) {
            $data = '';
        }

        return $data->format('d.m.Y H:i:s');
    }

    /**
     * формат d.m.Y H:i:s
     * @param CsvSerializationVisitor                 $visitor
     * @param                                          $data
     * @param array                                    $type
     * @param Context                                  $context
     *
     * @return mixed
     */
    public function serializeCsv(CsvSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        if (!($data instanceof DateTime)) {
            $data = '';
        }

        return $data->format('d.m.Y H:i:s');
    }


    /**
     * @param JsonDeserializationVisitor                 $visitor
     * @param                                            $data
     * @param array                                      $type
     * @param Context                                    $context
     *
     * @return mixed
     */
    public function deserialize(JsonDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        if (!($data instanceof DateTime)) {
            if (\strlen($data) > 0) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                try {
                    $data = new DateTime($data, 'd.m.Y H:i:s');
                } catch (ObjectException $e) {
                    $data = null;
                }
            } else {
                $data = null;
            }
        }
        
        return $data;
    }

    /**
     * @param XmlDeserializationVisitor                 $visitor
     * @param                                            $data
     * @param array                                      $type
     * @param Context                                    $context
     *
     * @return mixed
     */
    public function deserializeXml(XmlDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        if (!($data instanceof DateTime)) {
            if (\strlen($data) > 0) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                try {
                    $data = new DateTime($data, 'd.m.Y H:i:s');
                } catch (ObjectException $e) {
                    $data = null;
                }
            } else {
                $data = null;
            }
        }

        return $data;
    }

    /**
     * @param CsvDeserializationVisitor                 $visitor
     * @param                                            $data
     * @param array                                      $type
     * @param Context                                    $context
     *
     * @return mixed
     */
    public function deserializeCsv(CsvDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        if (!($data instanceof DateTime)) {
            if (\strlen($data) > 0) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                try {
                    $data = new DateTime($data, 'd.m.Y H:i:s');
                } catch (ObjectException $e) {
                    $data = null;
                }
            } else {
                $data = null;
            }
        }

        return $data;
    }
}
