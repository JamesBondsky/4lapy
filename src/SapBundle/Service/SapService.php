<?php

namespace FourPaws\SapBundle\Service;

use FourPaws\SapBundle\Consumer\ConsumerRegistryInterface;
use FourPaws\SapBundle\Exception\NotFoundPipelineException;
use FourPaws\SapBundle\Pipeline\PipelineRegistry;
use FourPaws\SapBundle\Source\SourceRegistryInterface;

/**
 * Class SapService
 *
 * @package FourPaws\SapBundle\Service
 */
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

    /**
     * @var PipelineRegistry
     */
    private $pipelineRegistry;

    public function __construct(
        ConsumerRegistryInterface $consumerRegistry,
        SourceRegistryInterface $sourceRegistry,
        PipelineRegistry $pipelineRegistry
    ) {
        $this->consumerRegistry = $consumerRegistry;
        $this->sourceRegistry = $sourceRegistry;
        $this->pipelineRegistry = $pipelineRegistry;
    }

    /**
     *
     * @param string $pipelineCode
     * @throws NotFoundPipelineException
     */
    public function execute(string $pipelineCode)
    {
        foreach ($this->pipelineRegistry->generator($pipelineCode) as $sourceMessage) {
            if ($this->consumerRegistry->consume($sourceMessage->getData())) {
                $this->sourceRegistry->ack($sourceMessage);

                continue;
            }

            $this->sourceRegistry->noAck($sourceMessage);
        }
    }
}
