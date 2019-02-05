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
     * @return $this
     */
    public function setId(int $id)
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
     * @return $this
     */
    public function setTitle(string $title)
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
     * @return $this
     * @throws \Bitrix\Main\SystemException
     */
    public function setWebPage(string $webPage)
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
     * @return $this
     */
    public function setXmlId(string $xmlId)
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
     * @return $this
     * @throws \Bitrix\Main\SystemException
     */
    public function setPicture(string $picture)
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
     * @return $this
     * @throws \Bitrix\Main\SystemException
     */
    public function setPicturePreview(string $picturePreview)
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
     * @return $this
     */
    public function setInPack(int $inPack)
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
     * @return $this
     */
    public function setPrice(Price $price)
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
     * @return $this
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
     * @return $this
     */
    public function setBrandName(string $brandName)
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
     * @return $this
     */
    public function setBonusUser(int $bonusUser)
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
     * @return $this
     */
    public function setBonusAll(int $bonusAll)
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
     * @return $this
     */
    public function setIsByRequest(bool $isByRequest)
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
     * @return $this
     */
    public function setIsAvailable(bool $isAvailable)
    {
        $this->isAvailable = $isAvailable;
        return $this;
    }
}
