<?php

namespace FourPaws\SapBundle\Serialization;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;

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
        ];
    }

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
}
