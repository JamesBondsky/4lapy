<?php

namespace FourPaws\SapBundle\Consumer;

use FourPaws\SapBundle\Exception\LogicException;
use FourPaws\SapBundle\Exception\UnexpectedValueException;
use FourPaws\SapBundle\Model\ConsumerCollection;

class ConsumerRegistry implements ConsumerRegistryInterface
{
    protected $collection;

    public function __construct()
    {
        $this->collection = new ConsumerCollection();
    }

    /**
     * @param ConsumerInterface $consumer
     *
     * @throws \InvalidArgumentException
     * @return ConsumerRegistryInterface
     */
    public function register(ConsumerInterface $consumer): ConsumerRegistryInterface
    {
        if (!$this->collection->contains($consumer)) {
            $this->collection->add($consumer);
        }
        return $this;
    }

    /**
     * @param $data
     *
     * @throws UnexpectedValueException
     * @throws LogicException
     * @return bool
     */
    public function consume($data): bool
    {
        return $this->get($data)->consume($data);
    }

    /**
     * @param $data
     *
     * @throws UnexpectedValueException
     * @throws LogicException
     * @return ConsumerInterface
     */
    protected function get($data): ConsumerInterface
    {
        $supported = $this->collection->filter(function (ConsumerInterface $consumer) use ($data) {
            return $consumer->support($data);
        });
        if ($supported->count() === 0) {
            throw new UnexpectedValueException('No such consumer for passed data');
        }

        if ($supported->count() > 1) {
            throw new LogicException('More than one consumer was found to passed data');
        }
        return $supported->first();
    }
}
