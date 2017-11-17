<?php

namespace FourPaws\Decorators;

use FourPaws\App\Application;
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
    
    private $width;
    
    private $height;
    
    /**
     * SvgDecorator constructor.
     *
     * @param string $image
     * @param int    $width
     * @param int    $height
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $image, int $width, int $height)
    {
        $this->setPath();
        $this->setImage($image);
        $this->setWidth($width);
        $this->setHeight($height);
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        $html = <<<html
<svg class="b-icon__svg" viewBox="0 0 {$this->getWidth()} {$this->getHeight()}" width="{$this->getWidth()}px" height="{$this->getHeight()}px">
    <use class="b-icon__use" xlink:href="{$this->path}#{$this->getImage()}"></use>
</svg>
html;
        
        return $html;
    }
    
    /**
     * Set svg file path
     *
     * @throws InvalidArgumentException;
     */
    public function setPath()
    {
        $this->path = Application::markup()->getSvgFile();
    }
    
    /**
     * @return string
     */
    public function getImage() : string
    {
        return $this->image;
    }
    
    /**
     * @param string $image
     */
    public function setImage(string $image)
    {
        $this->image = $image;
    }
    
    /**
     * @return int
     */
    public function getWidth() : int
    {
        return $this->width;
    }
    
    /**
     * @param int $width
     */
    public function setWidth(int $width)
    {
        $this->width = $width;
    }
    
    /**
     * @return int
     */
    public function getHeight() : int
    {
        return $this->height;
    }
    
    /**
     * @param int $height
     */
    public function setHeight(int $height)
    {
        $this->height = $height;
    }
}
