<?php

namespace FourPaws\CatalogBundle\Storage;


/**
 * Interface StorageInterface
 *
 * @package FourPaws\CatalogBundle\Storage
 */
interface StorageInterface
{
    /**
     * @param $data
     */
    public function save($data);

    /**
     * @param $key
     */
    public function load(string $key);
}
