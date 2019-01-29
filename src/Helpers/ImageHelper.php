<?php

namespace FourPaws\Helpers;

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
     * @param string $filePath
     * @return string
     * @see https://stackoverflow.com/a/4809562/2393499
     * @throws \ImagickException
     */
    public static function convertSvgToPng(string $filePath): string
    {
        // toDo when imagick will be installed...
        return $filePath;
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
        $im = new \Imagick();
        $svg = file_get_contents($filePath);
        $im->readImageBlob($svg);
        $im->setImageFormat('png24');
        // $im->writeImage('/path/to/colored/us-map.png');
        $im->clear();
        $im->destroy();
    }
}
