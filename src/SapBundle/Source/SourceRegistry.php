<?php

namespace FourPaws\SapBundle\Source;

use FourPaws\SapBundle\Exception\UnexpectedValueException;
use FourPaws\SapBundle\Model\SourceCollection;
use FourPaws\SapBundle\Model\SourceMessageInterface;

class SourceRegistry implements SourceRegistryInterface
{
    protected $collection;

    public function __construct()
    {
        $this->collection = new SourceCollection();
    }

    /**
     * @param string          $type
     * @param SourceInterface $source
     *
     * @throws \InvalidArgumentException
     * @return SourceRegistryInterface
     */
    public function register(string $type, SourceInterface $source): SourceRegistryInterface
    {
        $this->collection->set($type, $source);
        return $this;
    }

    /**
     * @param string $type
     *
     * @throws UnexpectedValueException
     * @return \Generator|SourceMessageInterface[]
     */
    public function generator(string $type): \Generator
    {
        yield from $this->get($type)->generator();
    }

    /**
     * @param SourceMessageInterface $sourceMessage
     *
     * @throws UnexpectedValueException
     * @return bool
     */
    public function ack(SourceMessageInterface $sourceMessage): bool
    {
        return $this->get($sourceMessage->getType())->ack($sourceMessage);
    }

    /**
     * @param SourceMessageInterface $sourceMessage
     *
     * @throws UnexpectedValueException
     * @return bool
     */
    public function noAck(SourceMessageInterface $sourceMessage): bool
    {
        return $this->get($sourceMessage->getType())->noAck($sourceMessage);
    }

    /**
     * @param string $type
     *
     * @throws UnexpectedValueException
     * @return SourceInterface
     */
    protected function get(string $type): SourceInterface
    {
        $source = $this->collection->get($type);
        if ($source) {
            return $source;
        }
        throw new UnexpectedValueException(sprintf('No such source for %s type', $type));
    }
}
