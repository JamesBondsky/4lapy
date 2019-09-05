<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 25.03.2019
 * Time: 16:18
 */

namespace FourPaws\PersonalBundle\Entity;


use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Basket;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\AppBundle\Entity\UserFieldEnumValue;
use FourPaws\AppBundle\Service\UserFieldEnumService;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\AppBundle\Traits\UserFieldEnumTrait;
use FourPaws\Helpers\DateHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Exception\NotFoundException;
use FourPaws\PersonalBundle\Exception\OrderSubscribeException;
use FourPaws\StoreBundle\Exception\NotFoundException as NotFoundStoreException;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\PersonalBundle\Service\OrderSubscribeHistoryService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Entity\User;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use FourPaws\App\Application;


/**
 * Class OrderSubscribeNextDelivery
 *
 * @package FourPaws\PersonalBundle\Repository
 */
class OrderSubscribeNextDelivery extends BaseEntity
{
    use UserFieldEnumTrait;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_SUBSCRIBE_ID")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $subscribeId;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_DATA")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $data;

    /**
     * @var array
     * @Serializer\Type("array<string>")
     * @Serializer\SerializedName("UF_OFFERS")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $offers;

    /**
     * @var array
     * @Serializer\Type("array<string>")
     * @Serializer\SerializedName("UF_QUANTITY")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $quantity;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_DATE_CREATE")
     * @Serializer\Groups(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateCreate;

    /**
     * @return int
     */
    public function getSubscribeId(): int
    {
        return $this->subscribeId;
    }

    /**
     * @param int $subscribeId
     * @return OrderSubscribeNextDelivery
     */
    public function setSubscribeId(int $subscribeId): OrderSubscribeNextDelivery
    {
        $this->subscribeId = $subscribeId;
        return $this;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     * @return OrderSubscribeNextDelivery
     */
    public function setData(string $data): OrderSubscribeNextDelivery
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getOffers(): array
    {
        return $this->offers;
    }

    /**
     * @param array $offers
     * @return OrderSubscribeNextDelivery
     */
    public function setOffers(array $offers): OrderSubscribeNextDelivery
    {
        $this->offers = $offers;
        return $this;
    }

    /**
     * @return array
     */
    public function getQuantity(): array
    {
        return $this->quantity;
    }

    /**
     * @param array $quantity
     * @return OrderSubscribeNextDelivery
     */
    public function setQuantity(array $quantity): OrderSubscribeNextDelivery
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreate(): DateTime
    {
        return $this->dateCreate;
    }

    /**
     * @param DateTime $dateCreate
     * @return OrderSubscribeNextDelivery
     */
    public function setDateCreate(DateTime $dateCreate): OrderSubscribeNextDelivery
    {
        $this->dateCreate = $dateCreate;
        return $this;
    }


}