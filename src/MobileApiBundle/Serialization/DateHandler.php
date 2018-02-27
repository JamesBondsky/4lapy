<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Serialization;

use JMS\Serializer\Handler\DateHandler as JMSDateHandler;
use JMS\Serializer\JsonDeserializationVisitor;

class DateHandler extends JMSDateHandler
{
    public function deserializeDateTimeFromJson(JsonDeserializationVisitor $visitor, $data, array $type)
    {
        if ((string)$data === '') {
            return '';
        }

        return parent::deserializeDateTimeFromJson($visitor, $data, $type);
    }
}
