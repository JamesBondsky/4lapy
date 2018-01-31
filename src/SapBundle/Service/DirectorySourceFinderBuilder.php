<?php

namespace FourPaws\SapBundle\Service;

use Symfony\Component\Finder\Finder;

class DirectorySourceFinderBuilder
{
    /**
     * @param string $nameMask
     * @param string $path
     * @param string $fileType
     *
     * @throws \InvalidArgumentException
     * @return Finder
     */
    public static function build(string $nameMask, string $path, string $fileType = 'xml')
    {
        return (new Finder())
            ->in($path)->name($nameMask . '.' . $fileType)
            ->files()
            ->ignoreDotFiles(true)->sortByName()
            ->filter(function (\SplFileInfo $file) {
                return $file->isReadable() && $file->isFile();
            });
    }
}
