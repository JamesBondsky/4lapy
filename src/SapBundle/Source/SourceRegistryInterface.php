<?php

namespace FourPaws\SapBundle\Source;

interface SourceRegistryInterface
{
    /**
     * @param string          $type
     * @param SourceInterface $source
     *
     * @return SourceRegistryInterface
     */
    public function register(string $type, SourceInterface $source) : SourceRegistryInterface;
    
    /**
     * @param string $type
     *
     * @return \Generator|SourceMessageInterface[]
     */
    public function generator(string $type) : \Generator;
    
    /**
     * @param SourceMessageInterface $sourceMessage
     *
     * @return bool
     */
    public function ack(SourceMessageInterface $sourceMessage) : bool;
    
    /**
     * @param SourceMessageInterface $sourceMessage
     *
     * @return bool
     */
    public function noAck(SourceMessageInterface $sourceMessage) : bool;
}
