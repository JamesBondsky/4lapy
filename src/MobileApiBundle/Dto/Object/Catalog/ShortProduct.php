<?php

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog;

use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct\Tag;
use FourPaws\MobileApiBundle\Dto\Object\Price;

class ShortProduct
{
    /**
     * Название
     * @var string
     */
    protected $title = '';

    /**
     * Абсолютный путь до товара
     * @var string
     */
    protected $webPage = '';

    /**
     * Артикул
     * @var string
     */
    protected $xmlId = '';

    /**
     * Ссылка на картинку превью (хорошее качество)
     * @var string
     */
    protected $picture = '';

    /**
     * Ссылка на картинку-превью (200*250 пикселей)
     * @var string
     */
    protected $picturePreview = '';

    /**
     * Количество в упаковке
     * @var int
     */
    protected $inPack = 1;

    /**
     * можно купить только упаковкой (кратным значению inPack)
     * @var bool
     */
    protected $packOnly = false;

    /**
     * Акционный текст
     * @var string
     */
    protected $discountText = '';

    /**
     * Краткое описание
     * @var string
     */
    protected $info = '';

    /**
     * ОбъектЦена
     * @var Price
     */
    protected $price;

    /**
     * @var Tag
     */
    protected $tag = [];

    /**
     * Размер бонуса для авторизованных, неавторизованных пользователей
     * @var int
     */
    protected $bonusUser = 0;

    /**
     * Размер бонуса для неавторизованных пользователей
     * @var int
     */
    protected $bonusAll = 0;
}
