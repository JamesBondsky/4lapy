<?php

namespace FourPaws\AppBundle\Serialization;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

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
                'method'    => 'deserialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'csv',
                'type'      => 'bitrix_bool_d7',
                'method'    => 'deserialize',
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
                'method'    => 'serializeCsv',
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
     * @param JsonSerializationVisitor $visitor
     * @param bool                     $data
     * @param array                    $type
     * @param Context                  $context
     *
     * @return bool
     */
    public function serializeCsv(JsonSerializationVisitor $visitor, bool $data, array $type, Context $context): bool
    {
        /** в csv вроде 1 или 0 */
        return $data ? 1 : 0;
    }
}
