<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog;

use Doctrine\Common\Collections\Collection;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct\Tag;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use JMS\Serializer\Annotation as Serializer;

class ShortProduct
{
    /**
     * Название
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     */
    protected $title = '';

    /**
     * Абсолютный путь до товара
     *
     * @var string
     * @Serializer\Type("string")
     * @todo path?
     */
    protected $webPage = '';

    /**
     * Артикул
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("xml_id")
     */
    protected $xmlId = '';

    /**
     * Ссылка на картинку превью (хорошее качество)
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("picture")
     */
    protected $picture = '';

    /**
     * Ссылка на картинку-превью (200*250 пикселей)
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("picture_preview")
     */
    protected $picturePreview = '';

    /**
     * Количество в упаковке
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("in_pack")
     */
    protected $inPack = 1;

    /**
     * можно купить только упаковкой (кратным значению inPack)
     *
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("pack_only")
     */
    protected $packOnly = false;

    /**
     * Акционный текст
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("discount_text")
     */
    protected $discountText = '';

    /**
     * Краткое описание
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("info")
     */
    protected $info = '';

    /**
     * ОбъектЦена
     *
     * @var Price
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Price")
     * @Serializer\SerializedName("price")
     */
    protected $price;

    /**
     * @var Collection|Tag[]
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct\Tag>")
     * @Serializer\SerializedName("tag")
     */
    protected $tag = [];

    /**
     * Размер бонуса для авторизованных, неавторизованных пользователей
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("bonus_user")
     */
    protected $bonusUser = 0;

    /**
     * Размер бонуса для неавторизованных пользователей
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("bonus_all")
     */
    protected $bonusAll = 0;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return ShortProduct
     */
    public function setTitle(string $title): ShortProduct
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getWebPage(): string
    {
        return $this->webPage;
    }

    /**
     * @param string $webPage
     *
     * @return ShortProduct
     */
    public function setWebPage(string $webPage): ShortProduct
    {
        $this->webPage = $webPage;
        return $this;
    }

    /**
     * @return string
     */
    public function getXmlId(): string
    {
        return $this->xmlId;
    }

    /**
     * @param string $xmlId
     *
     * @return ShortProduct
     */
    public function setXmlId(string $xmlId): ShortProduct
    {
        $this->xmlId = $xmlId;
        return $this;
    }

    /**
     * @return string
     */
    public function getPicture(): string
    {
        return $this->picture;
    }

    /**
     * @param string $picture
     *
     * @return ShortProduct
     */
    public function setPicture(string $picture): ShortProduct
    {
        $this->picture = $picture;
        return $this;
    }

    /**
     * @return string
     */
    public function getPicturePreview(): string
    {
        return $this->picturePreview;
    }

    /**
     * @param string $picturePreview
     *
     * @return ShortProduct
     */
    public function setPicturePreview(string $picturePreview): ShortProduct
    {
        $this->picturePreview = $picturePreview;
        return $this;
    }

    /**
     * @return int
     */
    public function getInPack(): int
    {
        return $this->inPack;
    }

    /**
     * @param int $inPack
     *
     * @return ShortProduct
     */
    public function setInPack(int $inPack): ShortProduct
    {
        $this->inPack = $inPack;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPackOnly(): bool
    {
        return $this->packOnly;
    }

    /**
     * @param bool $packOnly
     *
     * @return ShortProduct
     */
    public function setPackOnly(bool $packOnly): ShortProduct
    {
        $this->packOnly = $packOnly;
        return $this;
    }

    /**
     * @return string
     */
    public function getDiscountText(): string
    {
        return $this->discountText;
    }

    /**
     * @param string $discountText
     *
     * @return ShortProduct
     */
    public function setDiscountText(string $discountText): ShortProduct
    {
        $this->discountText = $discountText;
        return $this;
    }

    /**
     * @return string
     */
    public function getInfo(): string
    {
        return $this->info;
    }

    /**
     * @param string $info
     *
     * @return ShortProduct
     */
    public function setInfo(string $info): ShortProduct
    {
        $this->info = $info;
        return $this;
    }

    /**
     * @return Price
     */
    public function getPrice(): Price
    {
        return $this->price;
    }

    /**
     * @param Price $price
     *
     * @return ShortProduct
     */
    public function setPrice(Price $price): ShortProduct
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param Collection|Tag[] $tag
     *
     * @return ShortProduct
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return int
     */
    public function getBonusUser(): int
    {
        return $this->bonusUser;
    }

    /**
     * @param int $bonusUser
     *
     * @return ShortProduct
     */
    public function setBonusUser(int $bonusUser): ShortProduct
    {
        $this->bonusUser = $bonusUser;
        return $this;
    }

    /**
     * @return int
     */
    public function getBonusAll(): int
    {
        return $this->bonusAll;
    }

    /**
     * @param int $bonusAll
     *
     * @return ShortProduct
     */
    public function setBonusAll(int $bonusAll): ShortProduct
    {
        $this->bonusAll = $bonusAll;
        return $this;
    }
}
