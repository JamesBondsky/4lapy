<?php

namespace FourPaws\SapBundle\Serialization;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;

/**
 * Class SapBoolStringHandler
 *
 * @package FourPaws\SapBundle\Serialization
 */
class SapBoolStringHandler implements SubscribingHandlerInterface
{
    const DEFAULT_SAP_TRUE = 'X';
    const DEFAULT_SAP_FALSE = '';

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
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'xml',
                'type'      => 'sap_bool',
                'method'    => 'serialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'xml',
                'type'      => 'sap_bool',
                'method'    => 'deserialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => 'json',
                'type'      => 'sap_bool',
                'method'    => 'deserializeJson',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => 'sap_bool',
                'method'    => 'serializeJson',
            ],
        ];
    }

    /**
     * @param XmlSerializationVisitor $visitor
     * @param $data
     * @param array $type
     * @param Context $context
     *
     * @return mixed
     */
    public function serialize(XmlSerializationVisitor $visitor, $data, array $type, Context $context)
    {
        $data = $data ? self::DEFAULT_SAP_TRUE : self::DEFAULT_SAP_FALSE;
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
     * @param XmlDeserializationVisitor $visitor
     * @param $data
     * @param array $type
     * @param Context $context
     *
     * @return mixed
     */
    public function deserialize(XmlDeserializationVisitor $visitor, $data, array $type, Context $context)
    {
        $data = $data instanceof \SimpleXMLElement ? $data->__toString() : $data;
        $data = $data === static::DEFAULT_SAP_TRUE;

        return $visitor->getNavigator()->accept(
            (int)$data,
            [
                'name'   => 'bool',
                'params' => $type['params'],
            ],
            $context
        );
    }

    /**
     * @param VisitorInterface $visitor
     * @param $data
     * @param array $type
     * @param Context $context
     *
     * @return mixed
     */
    public function serializeJson(VisitorInterface $visitor, $data, array $type, Context $context)
    {
        return $visitor->getNavigator()->accept(
            $data,
            [
                'name'   => 'bool',
                'params' => $type['params'],
            ],
            $context
        );
    }

    /**
     * @param VisitorInterface $visitor
     * @param $data
     * @param array $type
     * @param Context $context
     *
     * @return mixed
     */
    public function deserializeJson(VisitorInterface $visitor, $data, array $type, Context $context)
    {
        $data = $data === static::DEFAULT_SAP_TRUE;

        return $visitor->getNavigator()->accept(
            (int)$data,
            [
                'name'   => 'bool',
                'params' => $type['params'],
            ],
            $context
        );
    }
}
