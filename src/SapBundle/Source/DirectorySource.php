<?php

namespace FourPaws\SapBundle\Source;

use FourPaws\SapBundle\Model\SourceMessageInterface;
use Symfony\Component\Finder\Finder;

class DirectorySource implements SourceInterface
{
    /**
     * @var Finder
     */
    private $inFinder;

    public function __construct(Finder $inFinder, string $success, string $error)
    {
        $this->inFinder = $inFinder;
    }

    /**
     * @param SourceMessageInterface $sourceMessage
     *
     * @return bool
     */
    public function ack(SourceMessageInterface $sourceMessage): bool
    {
        // TODO: Implement ack() method.
    }

    /**
     * @param SourceMessageInterface $sourceMessage
     *
     * @return bool
     */
    public function noAck(SourceMessageInterface $sourceMessage): bool
    {
        // TODO: Implement noAck() method.
    }

    /**
     * @return \Generator|SourceMessageInterface[]
     */
    public function generator()
    {
        // TODO: Implement generator() method.
    }
}
