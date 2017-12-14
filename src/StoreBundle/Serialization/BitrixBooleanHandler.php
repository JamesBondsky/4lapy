<?php

namespace FourPaws\StoreBundle\Serialization;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Context;

class BitrixBooleanHandler implements SubscribingHandlerInterface
{
    const BITRIX_TRUE = 'Y';

    const BITRIX_FALSE = 'N';

    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
                'type'      => 'bitrix_bool',
                'method'    => 'deserialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'bitrix_bool',
                'method'    => 'serialize',
            ],
        ];
    }

    public function serialize(JsonSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        $data = $data ? self::BITRIX_TRUE : self::BITRIX_FALSE;

        return $visitor->getNavigator()->accept(
            $data,
            [
                'name'   => 'string',
                'params' => $type['params'],
            ],
            $context
        );
    }

    public function deserialize(JsonDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        $data = $data == self::BITRIX_TRUE ? true : false;

        return $visitor->getNavigator()->accept(
            $data,
            [
                'name'   => 'bool',
                'params' => $type['params'],
            ],
            $context
        );
    }
}
