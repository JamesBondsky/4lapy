<?php

namespace FourPaws\SapBundle\Source;

use FourPaws\SapBundle\Model\SourceMessageInterface;
use Generator;
use RuntimeException;
use Symfony\Component\Finder\Finder;

/**
 * Class DirectorySource
 *
 * @todo Много вопросов по классу
 *
 * @package FourPaws\SapBundle\Source
 */
class DirectorySource implements SourceInterface
{
    /**
     * @var Finder
     */
    private $inFinder;
    
    /**
     * DirectorySource constructor.
     *
     * @param \Symfony\Component\Finder\Finder $inFinder
     * @param string                           $success
     * @param string                           $error
     */
    public function __construct(Finder $inFinder, string $success, string $error) {
        $this->inFinder = $inFinder;
    }
    
    /**
     * @param SourceMessageInterface $sourceMessage
     *
     * @return bool
     */
    public function ack(SourceMessageInterface $sourceMessage) : bool {
        // TODO: Implement ack() method.
    }
    
    /**
     * @param SourceMessageInterface $sourceMessage
     *
     * @return bool
     */
    public function noAck(SourceMessageInterface $sourceMessage) : bool {
        // TODO: Implement noAck() method.
    }
    
    /**
     * @throws RuntimeException
     *
     * @return SourceMessageInterface[]|Generator
     */
    public function generator() {
        foreach ($this->inFinder as $fileInfo) {
            yield new SourceMessage($fileInfo->getInode(), $fileInfo->getType(), $fileInfo->getContents());
        }
    }
}
