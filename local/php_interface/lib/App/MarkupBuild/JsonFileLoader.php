<?php

namespace FourPaws\App\MarkupBuild;

use RuntimeException;
use Symfony\Component\Config\FileLocatorInterface;

/**
 * Class JsonFileLoader
 * @package FourPaws\App\MarkupBuild
 *
 * TODO Изменить под 4 лапы
 */
class JsonFileLoader
{

    /**
     * @var MarkupBuild
     */
    private $markupBuild;

    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;

    public function __construct(MarkupBuild $markupBuild, FileLocatorInterface $fileLocator)
    {
        $this->markupBuild = $markupBuild;
        $this->fileLocator = $fileLocator;
    }

    public function load($file)
    {
        $path = $this->fileLocator->locate($file);

        $content = $this->loadFile($path);

        $config = json_decode($content);
        if (is_null($config)) {
            throw new RuntimeException(sprintf('Error decoding json from %s', $file));
        }

        if (isset($config->js)) {
            $this->markupBuild->withJsFile(MarkupBuild::STATIC_BUILD_DIR . '/' . trim($config->js));
        } else {
            throw new RuntimeException(sprintf('Missing `js` definition in %s', $file));
        }

        if (isset($config->css)) {
            $this->markupBuild->withCssFile(MarkupBuild::STATIC_BUILD_DIR . '/' . trim($config->css));
        } else {
            throw new RuntimeException(sprintf('Missing `css` definition in %s', $file));
        }

        if (isset($config->svg)) {
            $this->markupBuild->withSvgFile(MarkupBuild::STATIC_BUILD_DIR . '/' . trim($config->svg));
        } else {
            throw new RuntimeException(sprintf('Missing `svg` definition in %s', $file));
        }

    }

    private function loadFile($path)
    {
        $configJson = @file_get_contents($path);
        if (false === $configJson) {
            throw new RuntimeException(sprintf('Error loading markup build config from %s', $path));
        }

        return $configJson;
    }
}
