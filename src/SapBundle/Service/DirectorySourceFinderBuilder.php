<?php

namespace FourPaws\SapBundle\Service;

use Symfony\Component\Finder\Finder;

class DirectorySourceFinderBuilder
{
    public static function build(string $path, string $fileType = 'xml')
    {
        return (new Finder())
            ->in($path)
            ->name('*.' . $fileType)
            ->files()
            ->ignoreDotFiles(true)
            ->sortByAccessedTime()
            ->filter(function (\SplFileInfo $file) {
                return $file->isReadable() && $file->isFile();
            });
    }
}
