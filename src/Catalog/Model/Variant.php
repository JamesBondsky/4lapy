<?php

namespace FourPaws\Catalog\Model;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;

/**
 * Class Variant
 * 
 * @package FourPaws\Catalog\Model
 */
class Variant
{
    /**
     * @var string Название варианта фильтра
     */
    private $name = '';

    /**
     * @var string Значение варианта фильтра. Оно же является уникальным кодом варианта.
     */
    private $value = '';

    /**
     * @var int Количество продуктов, которые соответствуют этому варианту.
     */
    private $count = 0;

    /**
     * @var bool Вариант выбран.
     */
    private $checked = false;

    /**
     * @var bool Вариант доступен - его выбор даст непустой результат фильтрации.
     */
    private $available = true;

    /**
     * @var int ID файла картинки
     */
    private $image = 0;

    /**
     * @var string код цвета данного варианта
     */
    private $color = '';

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function withName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function withValue(string $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     *
     * @return $this
     */
    public function withCount(int $count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return bool
     */
    public function isChecked(): bool
    {
        return $this->checked;
    }

    /**
     * @param bool $checked
     *
     * @return $this
     */
    public function withChecked(bool $checked)
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * @param bool $available
     *
     * @return $this
     */
    public function withAvailable(bool $available)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * @return int
     */
    public function getImage(): int
    {
        return $this->image;
    }

    /**
     * @param int $image
     * @return $this
     */
    public function withImage(int $image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @param string $color
     *
     * @return $this
     */
    public function withColor(string $color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @param int $width
     * @param int $height
     *
     * @return string
     */
    public function getImageSrc(int $width, int $height): string
    {
        try {
            $result = ResizeImageDecorator::createFromPrimary($this->getImage())
                ->setResizeHeight($width)
                ->setResizeWidth($height)
                ->getSrc();
        } catch (FileNotFoundException $e) {
            $result = '';
        }

        return $result;
    }
}
