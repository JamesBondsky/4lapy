<?php

namespace FourPaws\App\MarkupBuild;

/**
 * Class MarkupBuild
 *
 * Информация о местонахождении сборок статики
 *
 * @package Adv\App\MarkupBuild
 *
 * TODO Изменить под 4 лапы
 */
class MarkupBuild
{
    /**
     * Папка, в которой собирается статика от корня сайта
     */
    const STATIC_BUILD_DIR = '/static/build';

    /**
     * Сборка js из dev-режима tars
     */
    const STATIC_DEV_JS = '/static/src/dev/static/js/main.js';

    /**
     * Сборка css из dev-режима tars
     */
    const STATIC_DEV_CSS = '/static/src/dev/static/css/main.css';

    /**
     * Сборка svg из dev-режима tars
     */
    const STATIC_DEV_SVG = '/static/src/dev/svg-symbols.svg';

    /**
     * Заглушка при отсутствии фото
     */
    const NO_PHOTO_IMG = '/static/img/assets/product-card/no-photo.png';

    /**
     * @var string
     */
    private $jsFile;

    /**
     * @var string
     */
    private $cssFile;

    /**
     * @var string
     */
    private $svgFile;

    /**
     * @var string
     */
    private $productNoImageFile;

    public function __construct()
    {
        $this->withProductNoImageFile(self::STATIC_BUILD_DIR . self::NO_PHOTO_IMG);
    }

    /**
     * @return string
     */
    public function getJsFile(): string
    {
        return $this->jsFile;
    }

    /**
     * @param string $jsFile
     *
     * @return MarkupBuild
     */
    public function withJsFile(string $jsFile): MarkupBuild
    {
        $this->jsFile = $jsFile;

        return $this;
    }

    /**
     * @return string
     */
    public function getCssFile(): string
    {
        return $this->cssFile;
    }

    /**
     * @param string $cssFile
     *
     * @return MarkupBuild
     */
    public function withCssFile(string $cssFile): MarkupBuild
    {
        $this->cssFile = $cssFile;

        return $this;
    }

    /**
     * @return string
     */
    public function getSvgFile(): string
    {
        return $this->svgFile;
    }

    /**
     * @param string $svgFile
     *
     * @return MarkupBuild
     */
    public function withSvgFile(string $svgFile): MarkupBuild
    {
        $this->svgFile = $svgFile;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductNoImageFile(): string
    {
        return $this->productNoImageFile;
    }

    /**
     * @param string $productNoImageFile
     *
     * @return MarkupBuild
     */
    public function withProductNoImageFile(string $productNoImageFile): MarkupBuild
    {
        $this->productNoImageFile = $productNoImageFile;

        return $this;
    }

}
