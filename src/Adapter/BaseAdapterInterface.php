<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.03.18
 * Time: 9:35
 */

namespace FourPaws\Adapter;

interface BaseAdapterInterface
{
    /**
     * BaseAdapter constructor.
     */
    public function __construct();

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