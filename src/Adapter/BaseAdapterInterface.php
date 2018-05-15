<?php

namespace FourPaws\Adapter;

/**
 * Interface BaseAdapterInterface
 *
 * @package FourPaws\Adapter
 */
interface BaseAdapterInterface
{
    /**
     * @param array  $data
     * @param string $class
     *
     * @return mixed
     */
    public function convertDataToEntity(array $data, string $class);

    /**
     * @param $entity
     *
     * @return array|mixed
     */
    public function convertEntityToData($entity);
}
