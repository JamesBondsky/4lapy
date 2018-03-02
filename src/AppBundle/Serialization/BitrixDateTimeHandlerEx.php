<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Serialization;

use Bitrix\Main\Type\DateTime;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class BitrixDateTimeHandlerEx
 * Обработчик для типа DateTime "Битрикса" с возможностью сброса значения
 * (для типа bitrix_date_time сбросить ранее установленное значение нельзя)
 *
 * @package FourPaws\AppBundle\Serialization
 */
class BitrixDateTimeHandlerEx implements SubscribingHandlerInterface
{
    /**
     * @return array
     */
    public static function getSubscribingMethods() : array
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'bitrix_date_time_ex',
                'method' => 'deserialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'bitrix_date_time_ex',
                'method' => 'serialize',
            ],
        ];
    }/** @noinspection MoreThanThreeArgumentsInspection */
    
    /**
     * @param JsonSerializationVisitor $visitor
     * @param $data
     * @param array $type
     * @param Context $context
     *
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor, $data, array $type, Context $context)
    {

        if (!($data instanceof DateTime)) {
            if ($data === '' || $data === false) {
                // чтобы можно было обнулить значение даты
                $data = '';
            } elseif (is_string($data) && $data !== '') {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $data = new DateTime($data, 'd.m.Y H:i:s');
            } else {
                $data = null;
            }
        }
        
        return $data;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param JsonDeserializationVisitor $visitor
     * @param $data
     * @param array $type
     * @param Context $context
     *
     * @return mixed
     */
    public function deserialize(JsonDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        if (!($data instanceof DateTime)) {
            if ($data === '' || $data === false) {
                // чтобы можно было обнулить значение даты
                $data = '';
            } elseif (is_string($data) && $data !== '') {
                /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                $data = new DateTime($data, 'd.m.Y H:i:s');
            } else {
                $data = null;
            }
        }
        
        return $data;
    }
}
