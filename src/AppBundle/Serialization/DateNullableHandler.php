<?php

namespace FourPaws\AppBundle\Serialization;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\DateHandler;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\XmlDeserializationVisitor;

class DateNullableHandler extends DateHandler
{
    public static function getSubscribingMethods(): array
    {
        $methods = [];
        $deserializationTypes = ['DateTimeNullable', 'DateTimeImmutableNullable', 'DateIntervalNullable'];
        $serializationTypes = ['DateTime', 'DateTimeImmutable', 'DateInterval'];

        foreach (array('json', 'xml', 'yml') as $format) {

            foreach ($deserializationTypes as $i => $type) {
                $methods[] = [
                    'type' => $type,
                    'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                    'format' => $format,
                ];

                $methods[] = array(
                    'type' => $type,
                    'format' => $format,
                    'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                    'method' => 'serialize' . $serializationTypes[$i],
                );
            }
        }

        return $methods;
    }

    public function deserializeDateTimeNullableFromXml(XmlDeserializationVisitor $visitor, $data, array $type)
    {
        return (string)$data ? parent::deserializeDateTimeFromXml($visitor, $data, $type) : new \DateTime();
    }

    public function deserializeDateTimeImmutableNullableFromXml(XmlDeserializationVisitor $visitor, $data, array $type)
    {
        return (string)$data ? parent::deserializeDateTimeImmutableFromXml($visitor, $data, $type) : new \DateTime();
    }

    public function deserializeDateIntervalNullableFromXml(XmlDeserializationVisitor $visitor, $data, array $type)
    {
        return (string)$data ? parent::deserializeDateIntervalFromXml($visitor, $data, $type) : new \DateTime();
    }

    public function deserializeDateTimeNullableFromJson(JsonDeserializationVisitor $visitor, $data, array $type)
    {
        return $data ? parent::deserializeDateTimeFromJson($visitor, $data, $type) : new \DateTime();
    }

    public function deserializeDateTimeImmutableNullableFromJson(JsonDeserializationVisitor $visitor, $data, array $type)
    {
        return $data ? parent::deserializeDateTimeImmutableFromJson($visitor, $data, $type) : new \DateTime();
    }

    public function deserializeDateIntervalNullableFromJson(JsonDeserializationVisitor $visitor, $data, array $type)
    {
        return $data ? parent::deserializeDateIntervalFromJson($visitor, $data, $type) : new \DateTime();
    }
}
