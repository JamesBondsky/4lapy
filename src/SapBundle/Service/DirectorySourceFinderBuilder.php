<?php

namespace FourPaws\SapBundle\Service;

use Symfony\Component\Finder\Finder;

class DirectorySourceFinderBuilder
{
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
