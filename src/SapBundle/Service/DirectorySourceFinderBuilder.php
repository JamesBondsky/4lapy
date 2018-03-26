<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service;

use InvalidArgumentException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class DirectorySourceFinderBuilder
{
    private $fileSystem;

    /**
     * @param string $nameMask
     * @param string $path
     * @param string $fileType
     *
     *
     * @throws InvalidArgumentException
     *
     * @throws IOException
     * @return Finder
     */
    public function build(string $nameMask, string $path, string $fileType = 'xml'): Finder
    {
        $this->checkPath($path);

        return (new Finder())
            ->in($path)->name(sprintf(
                '~%s\.%s$~i',
                $nameMask,
                $fileType
            ))
            ->depth(0)
            ->files()
            ->ignoreDotFiles(true)->sortByName()
            ->filter(function (\SplFileInfo $file) {
                return $file->isReadable() && $file->isFile();
            });
    }

    /**
     * DirectorySourceFinderBuilder constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->fileSystem = $filesystem;
    }

    /**
     * @param string $path
     *
     * @throws IOException
     */
    public function checkPath(string $path)
    {
        if (!$this->fileSystem->exists($path)) {
            $this->fileSystem->mkdir($path, '0775');
        }
    }
}
