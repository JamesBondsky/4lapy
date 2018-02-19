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
abstract class DirectorySource implements SourceInterface
{
    /**
     * @var Finder
     */
    protected $inFinder;

    /**
     * @var string
     */
    protected $success;

    /**
     * @var string
     */
    protected $error;

    /**
     * @var string
     */
    protected $type;

    /**
     * DirectorySource constructor.
     *
     * @param Finder $inFinder
     * @param string $type
     * @param string $success (success folder)
     * @param string $error (error folder)
     *
     * @throws RuntimeException
     */
    public function __construct(Finder $inFinder, string $type, string $success, string $error)
    {
        $this->inFinder = $inFinder;
        $this->type = $type;

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
    public function ack(SourceMessageInterface $sourceMessage): bool
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
    public function noAck(SourceMessageInterface $sourceMessage): bool
    {
        /**
         * @var FileSourceInterface $sourceMessage
         */
        $this->move($sourceMessage, $this->error);

        return true;
    }

    /**
     * @throws \RuntimeException
     *
     * @return Generator|SourceMessageInterface[]
     */
    public function generator()
    {
        foreach ($this->inFinder as $fileInfo) {
            yield (new FileSourceMessage(
                $fileInfo->getInode(),
                $this->type,
                $this->convert($fileInfo->getContents())
            ))
                ->setName($fileInfo->getFilename())
                ->setDirectory($fileInfo->getPath());
        }
    }

    /**
     * @param FileSourceInterface $source
     * @param string $destination
     */
    protected function move(FileSourceInterface $source, string $destination)
    {
        $from = $this->normalizePath($source->getDirectory()) . $source->getName();
        $to = $this->normalizePath($destination) . $source->getName();

        rename($from, $to);
    }

    /**
     * @param $destination
     *
     * @throws RuntimeException
     */
    protected function checkPath($destination)
    {
        if (!\is_dir($destination) && !\mkdir($destination) && !\is_dir($destination)) {
            throw new RuntimeException(sprintf('Wrong destination: %s', $destination));
        }
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function normalizePath(string $path): string
    {
        return sprintf('/%s/', trim($path, '/'));
    }

    abstract protected function convert($data);
}
