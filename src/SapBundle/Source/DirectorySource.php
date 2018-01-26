<?php

namespace FourPaws\SapBundle\Source;

use FourPaws\SapBundle\Exception\RuntimeException;
use Generator;
use Symfony\Component\Finder\Finder;

/**
 * Class DirectorySource
 *
 * @package FourPaws\SapBundle\Source
 */
class DirectorySource implements SourceInterface
{
    /**
     * @var Finder
     */
    private $inFinder;
    
    private $success;
    
    private $error;
    
    /**
     * DirectorySource constructor.
     *
     * @param Finder $inFinder
     * @param string $success (success folder)
     * @param string $error   (error folder)
     */
    public function __construct(Finder $inFinder, string $success, string $error)
    {
        $this->inFinder = $inFinder;
        
        $this->success = $success;
        $this->checkPath($success);
        
        $this->error = $error;
        $this->checkPath($error);
    }
    
    /**
     * @param SourceMessageInterface $sourceMessage
     *
     * @throws RuntimeException
     *
     * @return bool
     */
    public function ack(SourceMessageInterface $sourceMessage) : bool
    {
        /**
         * @var FileSourceInterface $sourceMessage
         */
        $this->move($sourceMessage, $this->success);
        
        return true;
    }
    
    /**
     * @param SourceMessageInterface $sourceMessage
     *
     * @throws RuntimeException
     *
     * @return bool
     */
    public function noAck(SourceMessageInterface $sourceMessage) : bool
    {
        /**
         * @var FileSourceInterface $sourceMessage
         */
        $this->move($sourceMessage, $this->error);
        
        return true;
    }
    
    /**
     * @throws \RuntimeException
     * @throws RuntimeException
     *
     * @return SourceMessageInterface[]|Generator
     */
    public function generator()
    {
        foreach ($this->inFinder as $fileInfo) {
            yield (new FileSourceMessage($fileInfo->getInode(),
                                         $fileInfo->getType(),
                                         $fileInfo->getContents()))->setName($fileInfo->getFilename())
                                                                   ->setDirectory($fileInfo->getPath());
        }
    }
    
    /**
     * @param FileSourceInterface $source
     * @param string              $destination
     */
    protected function move(FileSourceInterface $source, string $destination)
    {
        rename($source->getDirectory() . $source->getName(), $destination . $source->getName());
    }
    
    protected function checkPath($destination)
    {
        if (!\is_dir($destination) && !\mkdir($destination) && !\is_dir($destination)) {
            throw new RuntimeException(sprintf('Wrong destination: %s', $destination));
        }
    }
}
