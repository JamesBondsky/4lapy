<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

class TextProcessor
{
    /**
     * @param string $text
     *
     * @return string
     */
    public function processDetails(string $text): string
    {
        $text = trim($text);
        if ($text) {
            $text = htmlspecialchars_decode($text, ENT_COMPAT);
            $text = htmlspecialchars_decode($text, ENT_COMPAT);
            $text = strip_tags($text);
            $text = str_replace('"', '', $text);
            $text = trim($text);
            $text = htmlspecialcharsbx($text);
        }
        return $text;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function processHtml(string $text): string
    {
        $text = trim($text);
        $text = $this->correctionLinkInText($text);
        $text = htmlspecialcharsbx($text);
        return $text;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    protected function correctionLinkInText(string $text): string
    {
        if ($text) {
            $dom = new \DomDocument('1.0', 'utf-8');
            $text = mb_convert_encoding($text, 'HTML-ENTITIES', 'utf8') ?: '';
            $dom->loadHTML($text);

            foreach ($dom->getElementsByTagName('a') as $node) {
                /**
                 * @var \DOMElement $node
                 */
                $href = $node->getAttribute('href');

                if (strpos($href, 'http://') !== 0
                    && strpos($href, 'https://') !== 0
                    && strpos($href, 'mailto:') !== 0
                    && strpos($href, 'tel:') !== 0
                ) {
                    $node->setAttribute('href', 'https://' . SITE_SERVER_NAME . $href);
                }
            }

            foreach ($dom->getElementsByTagName('img') as $node) {
                $src = $node->getAttribute('src');

                if (strpos($src, 'http://') !== 0
                    && strpos($src, 'https://') !== 0
                ) {
                    $node->setAttribute('src', 'https://' . SITE_SERVER_NAME . $src);
                }
            }

            $text = preg_replace(['|^\<\!DOCTYPE.*?<html><body>|si', '|</body></html>$|si'], '', $dom->saveHTML()) ?: '';
            $text = mb_convert_encoding($text, 'utf8', 'HTML-ENTITIES') ?: '';

            unset($dom, $node, $href, $src);
        }
        return $text;
    }
}
