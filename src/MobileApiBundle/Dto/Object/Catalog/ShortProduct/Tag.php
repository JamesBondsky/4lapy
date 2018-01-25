<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;

class Tag
{
    /**
     * Идентификатор тега
     * @var int
     */
    protected $id;

    /**
     * Полный путь до изображения – графического представления тега
     * @var string
     */
    protected $img;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Tag
     */
    public function setId(int $id): Tag
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getImg(): string
    {
        return $this->img;
    }

    /**
     * @param string $img
     *
     * @return Tag
     */
    public function setImg(string $img): Tag
    {
        $this->img = $img;
        return $this;
    }
}
