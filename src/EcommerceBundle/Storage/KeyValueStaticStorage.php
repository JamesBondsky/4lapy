<?php

namespace FourPaws\EcommerceBundle\Storage;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class KeyValueStorage
 *
 * @package FourPaws\EcommerceBundle\Storage
 */
final class KeyValueStaticStorage
{
    /**
     * @var KeyValueStaticStorage
     */
    private static $instance;
    /**
     * @var ArrayCollection
     */
    private $collection;

    /**
     * KeyValueStorage constructor.
     *
     * @param ArrayCollection $collection
     */
    private function __construct(ArrayCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return KeyValueStaticStorage
     */
    public static function getInstance(): KeyValueStaticStorage
    {
        if (null === self::$instance) {
            self::$instance = new self(new ArrayCollection());
        }

        return self::$instance;
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->collection->get($key);
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value): void
    {
        $this->collection->set($key, $value);
    }

    /**
     * @param $key
     */
    public function remove($key): void
    {
        $this->collection->remove($key);
    }
}
