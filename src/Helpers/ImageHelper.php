<?php

namespace FourPaws\Helpers;

use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\Decorators\FullHrefDecorator;

class ImageHelper
{
    /**
     * Парсит все теги <img> в строке и добавляет домен в src, которые начинаются со слеша
     * @param string $html
     * @return string
     * @throws \Bitrix\Main\SystemException
     */
    public static function appendDomainToSrc(string $html): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        /** @see the reason why mb_convert_encoding() was used is described here https://stackoverflow.com/q/8218230/2393499 */
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        $images = $dom->getElementsByTagName('img');
        /** @var \DOMElement $image */
        foreach ($images as $image) {
            $src = $image->getAttribute('src');
            $src = (new FullHrefDecorator($src))->getFullPublicPath();
            $image->setAttribute('src', $src);
        }
        return $dom->saveHTML($dom->documentElement);
    }

    /**
     * Конвертирует svg изображение в png
     * @param string $svgFilePath
     * @return string
     * @see https://stackoverflow.com/a/4809562/2393499
     * @throws \ImagickException
     * @throws NotFoundException
     */
    public static function convertSvgToPng(string $svgFilePath): string
    {
        $svgFilePathInfo = pathinfo($svgFilePath);
        $svgFullFilePath = $_SERVER['DOCUMENT_ROOT'] . $svgFilePath;
        $pngFileDir = '/upload/svg2png' . $svgFilePathInfo['dirname'] . '/';
        $pngFilePath = $pngFileDir . $svgFilePathInfo['filename'] . '.png';

        \CheckDirPath($_SERVER['DOCUMENT_ROOT'] . $pngFileDir);


        $pngFullFilePath = $_SERVER['DOCUMENT_ROOT'] . $pngFilePath;
        if (file_exists($pngFullFilePath)) {
            return $pngFilePath;
        }
        if (!file_exists($svgFullFilePath)) {
            throw new NotFoundException("$svgFilePath does not exist");
        }

        $im = new \Imagick();
        $svg = file_get_contents($svgFullFilePath);

        if (strpos($svg, '<?xml') === false) {
            // handle invalid files
            $svg = '<?xml version="1.0" encoding="utf-8"?>' . $svg;
        }

        $im->readImageBlob($svg);
        $im->setImageFormat('png24');
        $im->writeImage($pngFullFilePath);
        $im->clear();
        $im->destroy();
        return $pngFilePath;
    }
}
