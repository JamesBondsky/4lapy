<?php

namespace FourPaws\SapBundle\Service;

use Symfony\Component\Finder\Finder;

class DirectorySourceFinderBuilder
{
    /**
     * @param string $prefix
     * @param string $path
     * @param string $fileType
     *
     * @return Finder
     *
     * @throws \InvalidArgumentException
     */
    public static function build(string $prefix, string $path, string $fileType = 'xml') : Finder
    {
        return (new Finder())
            ->in($path)
            ->name($prefix . '*.' . $fileType)
            ->files()
            ->ignoreDotFiles(true)
            ->sortByAccessedTime()
            ->filter(function (\SplFileInfo $file) {
                return $file->isReadable() && $file->isFile();
            });
    }
}
