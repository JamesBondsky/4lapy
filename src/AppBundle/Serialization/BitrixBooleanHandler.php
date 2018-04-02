<?php

namespace FourPaws\AppBundle\Serialization;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class BitrixBooleanHandler - конвертирует Y/N/1 в true/false и обратно
 * @package FourPaws\AppBundle\Serialization
 */
class BitrixBooleanHandler implements SubscribingHandlerInterface
{
    public const BITRIX_TRUE     = 'Y';
    
    public const BITRIX_TRUE_INT = '1';
    
    public const BITRIX_FALSE    = 'N';

    /**
     *
     *
     * @return array
     */
    public static function getSubscribingMethods(): array
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

    /**
     *
     *
     * @param JsonSerializationVisitor $visitor
     * @param $data
     * @param array $type
     * @param Context $context
     *
     * @return mixed
     */
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

    /**
     *
     *
     * @param JsonDeserializationVisitor $visitor
     * @param $data
     * @param array $type
     * @param Context $context
     *
     * @return mixed
     */
    public function deserialize(JsonDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        $data = ($data === self::BITRIX_TRUE || $data === self::BITRIX_TRUE_INT);
        
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
