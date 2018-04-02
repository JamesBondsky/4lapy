<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Trait SapOutFile
 *
 * @package FourPaws\SapBundle\Service
 */
trait SapOutFile
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param $entity
     *
     * @return string
     */
    abstract public function getFileName($entity): string;

    /**
     * @return string
     */
    public function getOutPrefix(): string
    {
        return $this->outPrefix;
    }

    /**
     * @param string $outPrefix
     */
    public function setOutPrefix(string $outPrefix): void
    {
        $this->outPrefix = $outPrefix;
    }

    /**
     * @param string $outPath
     *
     * @throws IOException
     */
    public function setOutPath(string $outPath): void
    {
        if (!$this->filesystem->exists($outPath)) {
            $this->filesystem->mkdir($outPath, '0775');
        }

        $this->outPath = $outPath;
    }

    /**
     * @param Filesystem $filesystem
     */
    public function setFilesystem(Filesystem $filesystem): void
    {
        $this->filesystem = $filesystem;
    }
}
