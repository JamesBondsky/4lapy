<?php

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

class ArrayOrFalseHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
                'type'      => 'array_or_false',
                'method'    => 'deserialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'xml',
                'type'      => 'array_or_false',
                'method'    => 'deserializeXml',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'csv',
                'type'      => 'array_or_false',
                'method'    => 'deserializeCsv',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'array_or_false',
                'method'    => 'serialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'xml',
                'type'      => 'array_or_false',
                'method'    => 'serializeXml',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'csv',
                'type'      => 'array_or_false',
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
        if ($data === false) {
            $data = [];
        }

        return $data;
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
        if ($data === false) {
            $data = [];
        }

        return $data;
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
        if ($data === false) {
            $data = [];
        }

        return $data;
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
        if ($data === false) {
            $data = [];
        }

        return $data;
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
        $list = [];
        if (!empty($data)) {
            $list = explode(',', $data);
        }

        return $list;
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
        $val = [];
        if (!empty($data)) {
            $val = implode(',', $data);
        }

        return $val;
    }
}
