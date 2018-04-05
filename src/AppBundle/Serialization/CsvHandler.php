<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\AppBundle\Serialization;

use JMS\Serializer\Context;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class CsvHandler
 * @package FourPaws\AppBundle\Serialization
 */
class CsvHandler implements SubscribingHandlerInterface
{
    public const DEFAULT_DELIMITER = ',';

    public const DEFAULT_ENCLOSURE = '"';

    public const DEFAULT_ESCAPE = '\\';

    /**
     * @return array
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'csv',
                'method' => 'deserialize',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'csv',
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
        $data = $visitor->getNavigator()->accept(
            $data,
            [
                'name' => 'array',
                'params' => [$type['params'][0]],
            ],
            $context
        );

        $result = [];
        if (!empty($data)) {
            $delimiter = $type['params'][1] ?? static::DEFAULT_DELIMITER;
            $enclosure = $type['params'][2] ?? static::DEFAULT_ENCLOSURE;
            $escape = $type['params'][3] ?? static::DEFAULT_ESCAPE;

            $header = array_keys(current($data));
            $result = array_map(function ($item) use ($header, $delimiter, $enclosure, $escape) {
                $result = [];
                foreach ($header as $key) {
                    $result[] = $this->prepareValue($item[$key]);
                }

                return $this->toCsv($result, $delimiter, $enclosure, $escape);
            }, $data);
            array_unshift($result, $this->toCsv($header, $delimiter, $enclosure, $escape));
        }

        return $result;
    }/** @noinspection MoreThanThreeArgumentsInspection */

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
        if (!\is_array($data)) {
            throw new RuntimeException('Data must be an array');
        }

        $result = [];
        if (!empty($data)) {
            $delimiter = $type['params'][1] ?? static::DEFAULT_DELIMITER;
            $enclosure = $type['params'][2] ?? static::DEFAULT_ENCLOSURE;
            $escape = $type['params'][3] ?? static::DEFAULT_ESCAPE;

            $data = array_map(function ($item) use ($delimiter, $enclosure, $escape) {
                return \is_array($item) ? $item : $this->fromCsv($item, $delimiter, $enclosure, $escape);
            }, $data);

            $header = array_shift($data);

            $result = \array_map(function ($item) use ($header) {
                return \array_combine($header, $item);
            }, $data);
        }

        $parameters = [$type['params'][0]];
        return $visitor->getNavigator()->accept(
            $result,
            [
                'name' => 'array',
                'params' => $parameters,
            ],
            $context
        );
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $value
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return array
     */
    protected function fromCsv(string $value, string $delimiter, string $enclosure, string $escape): array
    {
        return str_getcsv(
            $value,
            $delimiter,
            $enclosure,
            $escape
        );
    }

    /**
     * @param $value
     * @return string
     */
    protected function prepareValue($value): string
    {
        if (\is_array($value)) {
            $value = implode(',', $value);
        }

        return $value ?? '';
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $value
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return string
     */
    protected function toCsv(array $value, string $delimiter, string $enclosure, string $escape): string
    {
        $fp = fopen('php://memory', 'ab+');
        fputcsv($fp, $value, $delimiter, $enclosure, $escape);
        rewind($fp);
        return stream_get_contents($fp);
    }
}
