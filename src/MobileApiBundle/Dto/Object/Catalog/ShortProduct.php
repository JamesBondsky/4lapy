<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog;

use Doctrine\Common\Collections\Collection;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct\Tag;
use FourPaws\MobileApiBundle\Dto\Object\Price;
use JMS\Serializer\Annotation as Serializer;

class ShortProduct
{
    /**
     * ID
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     */
    protected $id;

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
     * @Serializer\SerializedName("webpage")
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
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("brand_name")
     */
    protected $brandName = '';

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
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("isByRequest")
     */
    protected $isByRequest = false;

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("isAvailable")
     */
    protected $isAvailable = false;

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
     * @return ShortProduct
     */
    public function setId(int $id): ShortProduct
    {
        $this->id = $id;
        return $this;
    }

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
     * @throws \Bitrix\Main\SystemException
     */
    public function setWebPage(string $webPage): ShortProduct
    {
        $hrefDecorator = new FullHrefDecorator($webPage);
        $this->webPage = $hrefDecorator->getFullPublicPath();
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
     * @throws \Bitrix\Main\SystemException
     */
    public function setPicture(string $picture): ShortProduct
    {
        if (!empty($picture)) {
            $hrefDecorator = new FullHrefDecorator($picture);
            $this->picture = $hrefDecorator->getFullPublicPath();
        }
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
     * @throws \Bitrix\Main\SystemException
     */
    public function setPicturePreview(string $picturePreview): ShortProduct
    {
        $hrefDecorator = new FullHrefDecorator($picturePreview);
        $this->picturePreview = $hrefDecorator->getFullPublicPath();
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
     * @return string
     */
    public function getBrandName()
    {
        return $this->brandName;
    }

    /**
     * @param string $brandName
     * @return ShortProduct
     */
    public function setBrandName(string $brandName): ShortProduct
    {
        $this->brandName = $brandName;
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

    /**
     * @return bool
     */
    public function getIsByRequest(): bool
    {
        return $this->isByRequest;
    }

    /**
     * @param bool $isByRequest
     * @return ShortProduct
     */
    public function setIsByRequest(bool $isByRequest): ShortProduct
    {
        $this->isByRequest = $isByRequest;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * @param bool $isAvailable
     * @return ShortProduct
     */
    public function setIsAvailable(bool $isAvailable): ShortProduct
    {
        $this->isAvailable = $isAvailable;
        return $this;
    }
}
