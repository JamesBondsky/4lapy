<?php

namespace FourPaws\BitrixOrm\Collection;

use Bitrix\Main\DB\Result;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;

class ResizeImageCollection extends D7CollectionBase
{
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    public function __construct(Result $result, int $width = 0, int $height = 0)
    {
        parent::__construct($result);
        $this->width = $width;
        $this->height = $height;
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
     * @return ResizeImageCollection
     */
    public function setWidth(int $width): ResizeImageCollection
    {
        $this->width = $width;
        $this->forAll(function ($key, ResizeImageDecorator $image) use ($width) {
            $image->setWidth($width);
        });
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
     * @return ResizeImageCollection
     */
    public function setHeight(int $height): ResizeImageCollection
    {
        $this->height = $height;
        $this->forAll(function ($key, ResizeImageDecorator $image) use ($height) {
            $image->setHeight($height);
        });
        return $this;
    }

    /**
     * Извлечение модели
     */
    protected function fetchElement(): \Generator
    {
        while ($fields = $this->getResult()->fetch()) {
            yield (new ResizeImageDecorator($fields))
                ->setHeight($this->height)
                ->setWidth($this->width);
        }
    }
}
