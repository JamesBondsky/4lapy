<?php

namespace FourPaws\AppBundle\Serialization;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;

class FormattedFloat implements SubscribingHandlerInterface
{
    protected const DECIMALS = 2;

    protected const DECIMAL_POINT = '.';

    protected const THOUSANDS_SEPARATOR = '';

    public static function getSubscribingMethods()
    {
        $result = [];
        $formats = ['json', 'xml', 'csv'];
        foreach ($formats as $format) {
            $result[] =             [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format'    => $format,
                'type'      => 'formatted_float',
                'method'    => 'deserialize',
            ];
            $result[] = [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => $format,
                'type'      => 'formatted_float',
                'method'    => 'serialize',
            ];
        }

        return $result;
    }

    /**
     * @param VisitorInterface $visitor
     * @param                          $data
     * @param array                    $type
     *
     * @return mixed
     */
    public function serialize(VisitorInterface $visitor, $data, array $type)
    {
        $decimals = $type['params'][0] ?? static::DECIMALS;
        $decimalPoint = $type['params'][1] ?? static::DECIMAL_POINT;
        $thousandsSeparator = $type['params'][2] ?? static::THOUSANDS_SEPARATOR;

        return number_format($data, $decimals, $decimalPoint, $thousandsSeparator);
    }

    /**
     * @param VisitorInterface $visitor
     * @param                            $data
     * @param array                      $type
     *
     * @return mixed
     */
    public function deserialize(VisitorInterface $visitor, $data, array $type)
    {
        $decimalPoint = $type['params'][0] ?? static::DECIMAL_POINT;
        $data = preg_replace('~[^\d' . $decimalPoint . ']~', '', $data);

        return (float)str_replace($decimalPoint, '.', $data);
    }
}
