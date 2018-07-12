<?php

namespace FourPaws\EcommerceBundle\Mapper;

/**
 * Interface ArrayMapperInterface
 *
 * @package FourPaws\EcommerceBundle\Mapper
 */
interface ArrayMapperInterface
{
    /**
     * ArrayMapper constructor.
     * [$to => $from, ...]
     *
     * @param array $map
     */
    public function __construct(array $map);

    /**
     * @param array $array
     * @param int $key
     *
     * @return array
     */
    public function map(array $array, $key = 0): array;

    /**
     * @param array $array
     *
     * @return iterable
     */
    public function mapCollection(array $array): array;
}
