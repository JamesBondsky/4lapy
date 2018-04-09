<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Serialization;

use FourPaws\AppBundle\SerializationVisitor\CsvDeserializationVisitor;
use FourPaws\AppBundle\SerializationVisitor\CsvSerializationVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;

class ArrayCommaString implements SubscribingHandlerInterface
{

    /**
     * Return format:
     *
     *      array(
     *          array(
     *              'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
     *              'format' => 'json',
     *              'type' => 'DateTime',
     *              'method' => 'serializeDateTimeToJson',
     *          ),
     *      )
     *
     * The direction and method keys can be omitted.
     *
     * @return array
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
                'type'      => 'array_comma_string',
                'method'    => 'deserialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'xml',
                'type'      => 'array_comma_string',
                'method'    => 'deserializeXml',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'csv',
                'type'      => 'array_comma_string',
                'method'    => 'deserializeCsv',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'array_comma_string',
                'method'    => 'serialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'xml',
                'type'      => 'array_comma_string',
                'method'    => 'serializeXml',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'csv',
                'type'      => 'array_comma_string',
                'method'    => 'serializeCsv',
            ],
        ];
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param                          $data
     * @param array                    $type
     * @param Context                  $context
     *
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        return \is_array($data) ? implode(',', $data) : '';
    }

    /**
     * @param JsonDeserializationVisitor $visitor
     * @param                            $data
     * @param array                      $type
     * @param Context                    $context
     *
     * @return mixed
     */
    public function deserialize(JsonDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        return \is_string($data) ? explode(',', $data) : [];
    }

    /**
     * @param XmlSerializationVisitor $visitor
     * @param                          $data
     * @param array                    $type
     * @param Context                  $context
     *
     * @return mixed
     */
    public function serializeXml(XmlSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        return \is_array($data) ? implode(',', $data) : '';
    }

    /**
     * @param XmlDeserializationVisitor $visitor
     * @param                            $data
     * @param array                      $type
     * @param Context                    $context
     *
     * @return mixed
     */
    public function deserializeXml(XmlDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        return \is_string($data) ? explode(',', $data) : [];
    }

    /**
     * @param CsvSerializationVisitor $visitor
     * @param                          $data
     * @param array                    $type
     * @param Context                  $context
     *
     * @return mixed
     */
    public function serializeCsv(CsvSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        return \is_array($data) ? implode(',', $data) : '';
    }

    /**
     * @param CsvDeserializationVisitor $visitor
     * @param                            $data
     * @param array                      $type
     * @param Context                    $context
     *
     * @return mixed
     */
    public function deserializeCsv(CsvDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        return \is_string($data) ? explode(',', $data) : [];
    }
}
