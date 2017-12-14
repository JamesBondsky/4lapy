<?php

namespace FourPaws\StoreBundle\Serialization;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Context;

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
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'array_or_false',
                'method'    => 'serialize',
            ],
        ];
    }

    public function serialize(JsonSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        if ($data === false) {
            $data = [];
        }

        return $visitor->getNavigator()->accept(
            $data,
            [
                'name'   => 'array',
                'params' => $type['params'],
            ],
            $context
        );
    }

    public function deserialize(JsonDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        if ($data === false) {
            $data = [];
        }

        return $visitor->getNavigator()->accept(
            $data,
            [
                'name'   => 'array',
                'params' => $type['params'],
            ],
            $context
        );
    }
}
