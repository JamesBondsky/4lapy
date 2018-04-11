<?php

namespace FourPaws\AppBundle\Serialization;

use FourPaws\AppBundle\DeserializationVisitor\CsvDeserializationVisitor;
use FourPaws\AppBundle\SerializationVisitor\CsvSerializationVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;

class BitrixBooleanD7Handler implements SubscribingHandlerInterface
{
    public const BITRIX_TRUE = 'Y';

    public const BITRIX_TRUE_INT = '1';

    /**
     * @return array
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
                'type'      => 'bitrix_bool_d7',
                'method'    => 'deserialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'xml',
                'type'      => 'bitrix_bool_d7',
                'method'    => 'deserializeXml',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'csv',
                'type'      => 'bitrix_bool_d7',
                'method'    => 'deserializeCsv',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'bitrix_bool_d7',
                'method'    => 'serialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'xml',
                'type'      => 'bitrix_bool_d7',
                'method'    => 'serializeXml',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'csv',
                'type'      => 'bitrix_bool_d7',
                'method'    => 'serializeCsv',
            ],
        ];
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param bool                     $data
     * @param array                    $type
     * @param Context                  $context
     *
     * @return bool
     */
    public function serialize(JsonSerializationVisitor $visitor, bool $data, array $type, Context $context): bool
    {
        return $data;
    }

    /**
     * @param XmlSerializationVisitor $visitor
     * @param bool                     $data
     * @param array                    $type
     * @param Context                  $context
     *
     * @return bool
     */
    public function serializeXml(XmlSerializationVisitor $visitor, bool $data, array $type, Context $context): bool
    {
        return $data ? 1 : 0;
    }

    /**
     * @param CsvSerializationVisitor $visitor
     * @param bool                     $data
     * @param array                    $type
     * @param Context                  $context
     *
     * @return bool
     */
    public function serializeCsv(CsvSerializationVisitor $visitor, bool $data, array $type, Context $context): bool
    {
        return $data ? 1 : 0;
    }

    /**
     * @param JsonDeserializationVisitor $visitor
     * @param string|int|bool            $data
     * @param array                      $type
     * @param Context                    $context
     *
     * @return mixed
     */
    public function deserialize(JsonDeserializationVisitor $visitor, $data, array $type, Context $context): bool
    {
        $data = ($data === self::BITRIX_TRUE || $data === self::BITRIX_TRUE_INT || $data === true);

        return $data;
    }

    /**
     * @param XmlDeserializationVisitor $visitor
     * @param string|int|bool            $data
     * @param array                      $type
     * @param Context                    $context
     *
     * @return mixed
     */
    public function deserializeXml(XmlDeserializationVisitor $visitor, $data, array $type, Context $context): bool
    {
        $data = ($data === self::BITRIX_TRUE || $data === self::BITRIX_TRUE_INT || $data === true);

        return $data;
    }

    /**
     * @param CsvDeserializationVisitor $visitor
     * @param string|int|bool            $data
     * @param array                      $type
     * @param Context                    $context
     *
     * @return mixed
     */
    public function deserializeCsv(CsvDeserializationVisitor $visitor, $data, array $type, Context $context): bool
    {
        $data = ($data === self::BITRIX_TRUE || $data === self::BITRIX_TRUE_INT || $data === true);

        return $data;
    }
}
