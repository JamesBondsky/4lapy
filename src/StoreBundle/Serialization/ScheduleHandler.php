<?php

namespace FourPaws\StoreBundle\Serialization;

use FourPaws\StoreBundle\Entity\Schedule;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

class ScheduleHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
                'type'      => 'store_schedule',
                'method'    => 'deserialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'store_schedule',
                'method'    => 'serialize',
            ],
        ];
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param JsonSerializationVisitor $visitor
     * @param $data
     * @param array $type
     * @param Context $context
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        if ($data instanceof Schedule) {
            $data = (string)$data;
        }

        return $visitor->getNavigator()->accept(
            (string)$data,
            [
                'name'   => 'string',
                'params' => $type['params'],
            ],
            $context
        );
    }/** @noinspection PhpUnusedParameterInspection */

    /**
     * @param $data
     * @return Schedule
     */
    public function deserialize(JsonDeserializationVisitor $visitor, $data): Schedule
    {
        return new Schedule((string)$data);
    }
}
