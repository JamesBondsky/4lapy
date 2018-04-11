<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Serialization;

use FourPaws\AppBundle\DeserializationVisitor\CsvDeserializationVisitor;
use FourPaws\AppBundle\SerializationVisitor\CsvSerializationVisitor;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;

class PhoneHandler implements SubscribingHandlerInterface
{
    /**
     * @return array
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
                'type'      => 'phone',
                'method'    => 'deserialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'xml',
                'type'      => 'phone',
                'method'    => 'deserializeXml',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'csv',
                'type'      => 'phone',
                'method'    => 'deserializeCsv',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'phone',
                'method'    => 'serialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'xml',
                'type'      => 'phone',
                'method'    => 'serializeXml',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'csv',
                'type'      => 'phone',
                'method'    => 'serializeCsv',
            ],
        ];
    }

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
        if ($data !== null && !empty($data)) {
            try {
                return PhoneHelper::normalizePhone($data);
            } catch (WrongPhoneNumberException $e) {
                return '';
            }
        }
        return '';
    }

    /**
     * @param XmlSerializationVisitor                 $visitor
     * @param                                          $data
     * @param array                                    $type
     * @param Context                                  $context
     *
     * @return mixed
     */
    public function serializeXml(XmlSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        if ($data !== null && !empty($data)) {
            try {
                return PhoneHelper::normalizePhone($data);
            } catch (WrongPhoneNumberException $e) {
                return '';
            }
        }
        return '';
    }

    /**
     * @param CsvSerializationVisitor                 $visitor
     * @param                                          $data
     * @param array                                    $type
     * @param Context                                  $context
     *
     * @return mixed
     */
    public function serializeCsv(CsvSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        if ($data !== null && !empty($data)) {
            try {
                return PhoneHelper::normalizePhone($data);
            } catch (WrongPhoneNumberException $e) {
                return '';
            }
        }
        return '';
    }

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
        if ($data !== null && !empty($data)) {
            try {
                return PhoneHelper::normalizePhone($data);
            } catch (WrongPhoneNumberException $e) {
                return '';
            }
        }
        return '';
    }

    /**
     * @param XmlDeserializationVisitor                 $visitor
     * @param                                            $data
     * @param array                                      $type
     * @param Context                                    $context
     *
     * @return mixed
     */
    public function deserializeXml(XmlDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        if ($data !== null && !empty($data)) {
            try {
                return PhoneHelper::normalizePhone($data);
            } catch (WrongPhoneNumberException $e) {
                return '';
            }
        }
        return '';
    }

    /**
     * @param CsvDeserializationVisitor                 $visitor
     * @param                                            $data
     * @param array                                      $type
     * @param Context                                    $context
     *
     * @return mixed
     */
    public function deserializeCsv(CsvDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        if ($data !== null && !empty($data)) {
            try {
                return PhoneHelper::normalizePhone($data);
            } catch (WrongPhoneNumberException $e) {
                return '';
            }
        }
        return '';
    }
}
