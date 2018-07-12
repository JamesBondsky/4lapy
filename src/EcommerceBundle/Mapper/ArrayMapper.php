<?php

namespace FourPaws\EcommerceBundle\Mapper;

use Generator;

/**
 * Class ArrayMapper
 *
 * @package FourPaws\EcommerceBundle\Utils
 */
class ArrayMapper implements ArrayMapperInterface
{
    /**
     * @var array
     */
    private $map;

    /**
     * ArrayMapper constructor.
     * [$to => $from, ...]
     *
     * @param array $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * @param array $array
     * @param int $key
     *
     * @return array
     */
    public function map(array $array, $key = 0): array
    {
        return \array_reduce(\iterator_to_array($this->mapInternal($array, $key)), function ($carry, $item){
            return \array_merge($carry, $item);
        }, []);
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public function mapCollection(array $array): array {
        \array_walk($array, function(&$element, $key) {
            $element = $this->map($element, $key);

            return $element;
        });

        return $array;
    }

    /**
     * @param $array
     * @param int $key
     *
     * @return Generator
     */
    protected function mapInternal($array, $key = 0): Generator
    {
        foreach ($this->map as $to => $from) {
            yield [$to => \is_callable($from) ? $from($array, $key) : $array[$from]];
        }
    }
}
