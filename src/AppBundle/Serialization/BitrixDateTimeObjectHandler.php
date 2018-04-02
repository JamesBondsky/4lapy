<?php
/**
 * Created by PhpStorm.
 * Date: 29.03.2018
 * Time: 17:35
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\AppBundle\Serialization;

use Bitrix\Main\Type\DateTime as BitrixDateTime;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class BitrixDateTimeObjectHandler - конвертирует Bitrix\Main\Type\DateTime в \DateTime и обратно
 * @package FourPaws\AppBundle\Serialization
 */
class BitrixDateTimeObjectHandler implements SubscribingHandlerInterface
{
    /**
     * @return array
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'bitrix_date_time_object',
                'method' => 'deserialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'bitrix_date_time_object',
                'method' => 'serialize',
            ],
        ];
    }

    /**
     * @noinspection MoreThanThreeArgumentsInspection
     *
     * @param JsonSerializationVisitor $visitor
     * @param                                          $data
     * @param array $type
     * @param Context $context
     *
     * @return mixed
     */
    public function serialize(
        /** @noinspection PhpUnusedParameterInspection */
        JsonSerializationVisitor $visitor,
        $data,
        array $type,
        Context $context
    ) {
        if ($data instanceof \DateTime) {
            $data = BitrixDateTime::createFromPhp($data);
        } else {
            $data = null;
        }
        /**
         * @todo возвращать как положено
         * @todo разобраться че там с конвертацией в строки для JSON etc.
         */
        return $data;
    }


    /**
     * @noinspection MoreThanThreeArgumentsInspection
     *
     * @param JsonDeserializationVisitor $visitor
     * @param                                            $data
     * @param array $type
     * @param Context $context
     *
     * @return mixed
     */
    public function deserialize(
        /** @noinspection PhpUnusedParameterInspection */
        JsonDeserializationVisitor $visitor,
        $data,
        array $type,
        Context $context
    ) {
        if ($data instanceof BitrixDateTime) {
            $data = new \DateTime();
            $data->setTimestamp($data->getTimestamp());
        } else {
            $data = null;
        }

        return $data;
    }
}