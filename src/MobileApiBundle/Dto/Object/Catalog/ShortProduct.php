<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog;

use Doctrine\Common\Collections\Collection;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct\StampLevel;
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
     * @Serializer\Groups({"specialOffers", "productsList", "product"})
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
     * @Serializer\Groups({"specialOffers", "productsList", "product"})
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
     * Является ли товаром под заказ?
     *
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("isByRequest")
     */
    protected $isByRequest = false;

    /**
     * Доступен ли товар к покупке (есть в магазине, либо на складе)
     *
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("isAvailable")
     */
    protected $isAvailable = false;

    /**
     * Только самовывоз (товара нет на складе, есть только в некоторых магазинах)
     *
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("isPickup")
     */
    protected $pickupOnly = false;

    /**
     * Акция в рамках которой товар является подарком (только для корзины)
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("giftDiscountId")
     * @Serializer\Groups({"basket"})
     */
    protected $giftDiscountId = 0;

    /**
     * Количество бесплатных товаров в рамках акций n+1
     *
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("freeGoodsAmount")
     * @Serializer\Groups({"basket"})
     */
    protected $freeGoodsAmount = 0;

    /**
     * ОбъектЦвет
     *
     * @var \FourPaws\MobileApiBundle\Dto\Object\Color
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Color")
     */
    protected $color;

    /**
     * @var StampLevel[]
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct\StampLevel>")
     * @Serializer\SerializedName("stampLevels")
     */
    protected $stampLevels = [];

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\Exclude()
     */
    protected $usedStamps = 0;
    
    /**
     * Средняя оценка товара
     * @var integer
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("total_stars")
     * @Serializer\SkipWhenEmpty()
     */
    protected $totalStars = 0;
    
    /**
     * Всего отзывов о товаре
     * @var integer
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("total_comments")
     * @Serializer\SkipWhenEmpty()
     */
    protected $totalComments = 0;


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
     */
    public function setWebPage(string $webPage)
    {
        $this->webPage = (string) new FullHrefDecorator($webPage);
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
     */
    public function setPicture(string $picture)
    {
        $this->picture = (string) new FullHrefDecorator($picture);
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
     */
    public function setPicturePreview(string $picturePreview)
    {
        $this->picturePreview = (string) new FullHrefDecorator($picturePreview);
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

    /**
     * @return bool
     */
    public function getPickupOnly(): bool
    {
        return $this->pickupOnly;
    }

    /**
     * @param bool $pickupOnly
     * @return $this
     */
    public function setPickupOnly(bool $pickupOnly)
    {
        $this->pickupOnly = $pickupOnly;
        return $this;
    }

    /**
     * @return bool
     */
    public function getGiftDiscountId()
    {
        return $this->giftDiscountId;
    }

    /**
     * @param int $giftDiscountId
     * @return $this
     */
    public function setGiftDiscountId(int $giftDiscountId)
    {
        $this->giftDiscountId = $giftDiscountId;
        return $this;
    }

    /**
     * @return bool
     */
    public function getFreeGoodsAmount()
    {
        return $this->freeGoodsAmount;
    }

    /**
     * @param int $freeGoodsAmount
     * @return $this
     */
    public function setFreeGoodsAmount(int $freeGoodsAmount)
    {
        $this->freeGoodsAmount = $freeGoodsAmount;
        return $this;
    }

    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }

    public function getColor()
    {
        return $this->color;
    }

    /**
     * @return StampLevel[]
     */
    public function getStampLevels(): array
    {
        return $this->stampLevels;
    }

    /**
     * @param StampLevel[] $stampLevels
     * @return ShortProduct
     */
    public function setStampLevels(array $stampLevels): ShortProduct
    {
        $this->stampLevels = $stampLevels;
        return $this;
    }

    /**
     * @param int $usedStamps
     * @return ShortProduct
     */
    public function setUsedStamps(int $usedStamps): ShortProduct
    {
        $this->usedStamps = $usedStamps;
        return $this;
    }

    /**
     * @return int
     */
    public function getUsedStamps(): int
    {
        return $this->usedStamps;
    }
    
    /**
     * @return integer
     */
    public function getTotalStars(): int
    {
        return $this->totalStars;
    }
    
    /**
     * @param integer
     *
     * @return ShortProduct
     */
    public function setTotalStars(int $totalStars): ShortProduct
    {
        $this->totalStars = $totalStars;
        return $this;
    }
    
    /**
     * @return integer
     */
    public function getTotalComments(): int
    {
        return $this->totalComments;
    }
    
    /**
     * @param integer
     *
     * @return ShortProduct
     */
    public function setTotalComments(int $totalComments): ShortProduct
    {
        $this->totalComments = $totalComments;
        return $this;
    }
}
