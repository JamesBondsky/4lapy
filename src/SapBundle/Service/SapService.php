<?php

namespace FourPaws\SapBundle\Service;

use FourPaws\SapBundle\Consumer\ConsumerRegistryInterface;
use FourPaws\SapBundle\Dto\In\Offers\Materials;
use FourPaws\SapBundle\Dto\In\Prices\Prices;
use FourPaws\SapBundle\Model\SourceMessageInterface;
use FourPaws\SapBundle\Source\SourceRegistryInterface;

class SapService
{
    /**
     * @var ConsumerRegistryInterface
     */
    private $consumerRegistry;
    /**
     * @var SourceRegistryInterface
     */
    private $sourceRegistry;

    public function __construct(ConsumerRegistryInterface $consumerRegistry, SourceRegistryInterface $sourceRegistry)
    {
        $this->consumerRegistry = $consumerRegistry;
        $this->sourceRegistry = $sourceRegistry;
    }

    public function processIn()
    {
        foreach ($this->inPipeLine() as $sourceMessage) {
            if ($this->consumerRegistry->consume($sourceMessage->getData())) {
                $this->sourceRegistry->ack($sourceMessage);
                continue;
            }
            $this->sourceRegistry->noAck($sourceMessage);
        }
    }

    /**
     * @return \Generator|SourceMessageInterface[]
     */
    protected function inPipeLine()
    {
        yield from $this->sourceRegistry->generator(Materials::class);
//        yield from $this->sourceRegistry->generator(Prices::class);
        //yield from $this->source->get(Remains::class);
        //yield from $this->source->get(Actions::class);
        //
    }
}
