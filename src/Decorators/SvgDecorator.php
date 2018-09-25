<?php

namespace FourPaws\Decorators;

use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use Psr\Cache\InvalidArgumentException;

/**
 * Project specific SvgDecorator
 *
 * @package FourPaws\Decorators
 */
class SvgDecorator
{
    private $path;
    private $image;
    private $width  = 0;
    private $height = 0;
    private $domain;

    /**
     * SvgDecorator constructor.
     *
     * @param string $image
     * @param int    $width
     * @param int    $height
     */
    public function __construct(string $image, int $width = 0, int $height = 0)
    {
        $this->setPath();
        $this->setImage($image);

        if ($width) {
            $this->setWidth($width);
        }

        if ($height) {
            $this->setHeight($height);
        }

        $this->setDomain();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $viewBox = ($this->getWidth() || $this->getHeight()) ? <<<viewbox
viewBox="0 0 {$this->getWidth()} {$this->getHeight()}" width="{$this->getWidth()}px" height="{$this->getHeight()}px"
viewbox
            : '';

        $html = <<<html
<svg class="b-icon__svg"{$viewBox}>
    <use class="b-icon__use" xlink:href="{$this->domain}{$this->path}#{$this->getImage()}"></use>
</svg>
html;

        return $html;
    }

    /**
     * Set svg file path
     */
    public function setPath()
    {
        try {
            $this->path = Application::markup()
                                     ->getSvgFile();
        } catch (Exception | InvalidArgumentException $e) {
            $this->path = '';
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     *
     * @return $this
     */
    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     *
     * @return $this
     */
    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     *
     * @return $this
     */
    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param $domain
     *
     * @return $this
     */
    public function setDomain(string $domain = ''): self
    {
        static $baseDomain;

        if ($domain) {
            $this->domain = $domain;
        } elseif ($baseDomain) {
            $this->domain = $baseDomain;
        } else {
            try {
                $baseDomain = Application::getInstance()->getSiteDomain();
            } catch (ApplicationCreateException $e) {
                $baseDomain = '';
            }

            $this->domain = $baseDomain;
        }

        return $this;
    }
}
