<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Serialization;

use Bitrix\Main\Type\Date;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

class BitrixDateHandler implements SubscribingHandlerInterface
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
                'type'      => 'bitrix_date',
                'method'    => 'deserialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'bitrix_date',
                'method'    => 'serialize',
            ],
        ];
    }/** @noinspection MoreThanThreeArgumentsInspection */
    
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
        if (!($data instanceof Date)) {
            $data = null;
        }
        
        return $data;
    }/** @noinspection MoreThanThreeArgumentsInspection */
    
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
        if (!($data instanceof Date)) {
            if (\strlen($data) > 0) {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $data = new Date($data, 'd.m.Y');
            } else {
                $data = null;
            }
        }
        
        return $data;
    }
}
