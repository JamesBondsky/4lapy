<?php

namespace FourPaws\SapBundle\Source;

interface SourceInterface
{
    /**
     * @return \Generator|SourceMessageInterface[]
     */
    public function generator();
    
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
