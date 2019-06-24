<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 21.06.2019
 * Time: 14:08
 */

namespace FourPaws\MobileApiBundle\Dto\Object\OrderSubscribe;


use FourPaws\MobileApiBundle\Dto\Object\Price;
use FourPaws\MobileApiBundle\Services\Api\ProductService;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use FourPaws\PersonalBundle\Entity\OrderSubscribeItem as PersonalOrderSubscribeItem;

class OrderSubscribeItem
{
    use PropertiesFillingTrait;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("subscribeId")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $subscribeId;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("offerId")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $offerId;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("quantity")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $quantity;

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
     * Ссылка на картинку-превью (200*250 пикселей)
     *
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("picture_preview")
     */
    protected $picturePreview = '';
    /**
     * ОбъектЦена
     *
     * @var Price
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\Price")
     * @Serializer\SerializedName("price")
     */
    protected $price;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("brand_name")
     */
    protected $brandName = '';

    /**
     * OrderSubscribeItem constructor.
     * @param $transferObject
     * @throws \Exception
     */
    public function __construct(PersonalOrderSubscribeItem $orderSubscribe)
    {
        $this->fillProperties($orderSubscribe);

        $offer = $orderSubscribe->getOffer();
        $this
            ->setTitle($offer->getName())
            ->setXmlId($offer->getXmlId())
            ->setBrandName($offer->getProduct()->getBrandName())
            ->setWebPage($offer->getCanonicalPageUrl())
        ;

        // картинка ресайз (возможно не используется, но это не точно)
        if ($resizeImages = $offer->getResizeImages(ProductService::LIST_IMAGE_WIDTH, ProductService::LIST_IMAGE_HEIGHT)) {
            $this->setPicturePreview($resizeImages->first());
        }

        // цена
        $price = (new Price())
            ->setActual($offer->getPrice())
            ->setOld($offer->getOldPrice())
            ->setSubscribe($offer->getSubscribePrice());
        $this->setPrice($price);
    }


    /**
     * @return int
     */
    public function getSubscribeId(): int
    {
        return $this->subscribeId;
    }

    /**
     * @param int $subscribeId
     */
    public function setSubscribeId(int $subscribeId): OrderSubscribeItem
    {
        $this->subscribeId = $subscribeId;
        return $this;
    }

    /**
     * @return int
     */
    public function getOfferId(): int
    {
        return $this->offerId;
    }

    /**
     * @param int $offerId
     */
    public function setOfferId(int $offerId): OrderSubscribeItem
    {
        $this->offerId = $offerId;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): OrderSubscribeItem
    {
        $this->quantity = $quantity;
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
     * @return OrderSubscribeItem
     */
    public function setTitle(string $title): OrderSubscribeItem
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
     * @return OrderSubscribeItem
     */
    public function setWebPage(string $webPage): OrderSubscribeItem
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
     * @return OrderSubscribeItem
     */
    public function setXmlId(string $xmlId): OrderSubscribeItem
    {
        $this->xmlId = $xmlId;
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
     * @return OrderSubscribeItem
     */
    public function setPicturePreview(string $picturePreview): OrderSubscribeItem
    {
        $this->picturePreview = $picturePreview;
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
     * @return OrderSubscribeItem
     */
    public function setPrice(Price $price): OrderSubscribeItem
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return string
     */
    public function getBrandName(): string
    {
        return $this->brandName;
    }

    /**
     * @param string $brandName
     * @return OrderSubscribeItem
     */
    public function setBrandName(string $brandName): OrderSubscribeItem
    {
        $this->brandName = $brandName;
        return $this;
    }
}