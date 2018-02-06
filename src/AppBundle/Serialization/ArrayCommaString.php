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
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'array_comma_string',
                'method'    => 'serialize',
            ],
        ];
    }

    public function serialize(JsonSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        return $visitor->getNavigator()->accept(
            \is_array($data) ? implode(',', $data) : '',
            [
                'name'   => 'string',
                'params' => $type['params'],
            ],
            $context
        );
    }

    public function deserialize(JsonDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        return $visitor->getNavigator()->accept(
            \is_string($data) ? explode(',', $data) : [],
            [
                'name'   => 'array',
                'params' => $type['params'],
            ],
            $context
        );
    }
}
