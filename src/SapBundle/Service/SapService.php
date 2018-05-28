<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service;

use FourPaws\AppBundle\Service\LockerInterface;
use FourPaws\SaleBundle\Discount\Utils\Manager;
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

    /**
     * SapService constructor.
     *
     * @param ConsumerRegistryInterface $consumerRegistry
     * @param SourceRegistryInterface $sourceRegistry
     * @param PipelineRegistry $pipelineRegistry
     * @param LockerInterface $locker
     */
    public function __construct(
        ConsumerRegistryInterface $consumerRegistry,
        SourceRegistryInterface $sourceRegistry,
        PipelineRegistry $pipelineRegistry
    )
    {
        $this->consumerRegistry = $consumerRegistry;
        $this->sourceRegistry = $sourceRegistry;
        $this->pipelineRegistry = $pipelineRegistry;
    }

    /**
     * @param string $pipelineCode
     * @throws NotFoundPipelineException
     */
    public function execute(string $pipelineCode): void
    {
        Manager::disableExtendsDiscount();

        foreach ($this->pipelineRegistry->generator($pipelineCode) as $sourceMessage) {
            if ($this->consumerRegistry->consume($sourceMessage->getData())) {
                $this->sourceRegistry->ack($sourceMessage);

                continue;
            }

            $this->sourceRegistry->noAck($sourceMessage);
        }
    }
}
