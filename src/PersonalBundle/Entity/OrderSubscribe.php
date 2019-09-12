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

class OrderSubscribe extends BaseEntity
{
    use UserFieldEnumTrait;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_USER_ID")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $userId;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_DEL_TYPE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $deliveryId;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_FREQUENCY")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $frequency;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_DELIVERY_TIME")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $deliveryTime;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_DEL_PLACE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $deliveryPlace;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_LOCATION")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Assert\NotBlank(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $locationId;

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("UF_ACTIVE")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $active = true;

    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("UF_ORDER_ID")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $orderId;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_NEXT_DEL")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $nextDate;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_DATE_CREATE")
     * @Serializer\Groups(groups={"create","read"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateCreate;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_DATE_EDIT")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $dateUpdate;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_LAST_CHECK")
     * @Serializer\Groups(groups={"create","read","update"})
     */
    protected $lastCheck;

    /**
     * @var bool
     * @Serializer\Type("bool")
     * @Serializer\SerializedName("UF_BONUS")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $payWithbonus = false;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_CHECK_DAYS")
     * @Serializer\Groups(groups={"create","read","update"})
     * @Serializer\SkipWhenEmpty()
     */
    protected $checkDays;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time_ex")
     * @Serializer\SerializedName("UF_DATE_CHECK")
     * @Serializer\Groups(groups={"create","read","update"})
     */
    protected $dateCheck;


    /**
     * @var UserFieldEnumService $userFieldEnumService
     * @Serializer\Exclude()
     */
    private $userFieldEnumService;

    /**
     * @var null|Order $order
     * @Serializer\Exclude()
     */
    private $order;

    /**
     * @var UserFieldEnumValue $deliveryFrequencyEntity
     * @Serializer\Exclude()
     */
    private $deliveryFrequencyEntity;

    /**
     * @var User $user
     * @Serializer\Exclude()
     */
    private $user;

    /**
     * @return array
     */
    public function getAllFields() : array
    {
        $fields = [
            'ID' => $this->getId(),
            'UF_ORDER_ID' => $this->getOrderId(),
            'UF_DATE_CREATE' => $this->getDateCreate(),
            'UF_DATE_UPDATE' => $this->getDateUpdate(),
            'UF_FREQUENCY' => $this->getFrequency(),
            'UF_DELIVERY_TIME' => $this->getDeliveryTime(),
            'UF_ACTIVE' => $this->isActive(),
            'UF_LAST_CHECK' => $this->getLastCheck(),
        ];

        return $fields;
    }

    /**
     * @return int
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return OrderSubscribe
     */
    public function setUserId(int $userId): OrderSubscribe
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryId(): ?int
    {
        return $this->deliveryId;
    }

    /**
     * @param int $deliveryId
     * @return OrderSubscribe
     */
    public function setDeliveryId($deliveryId): OrderSubscribe
    {
        $this->deliveryId = $deliveryId;
        return $this;
    }

    /**
     * @return int
     */
    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    /**
     * @param int $frequency
     * @return OrderSubscribe
     */
    public function setFrequency(int $frequency): OrderSubscribe
    {
        $this->frequency = $frequency;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryTime(): ?string
    {
        return $this->deliveryTime;
    }

    /**
     * @param string $deliveryTime
     * @return OrderSubscribe
     */
    public function setDeliveryTime($deliveryTime): OrderSubscribe
    {
        $this->deliveryTime = $deliveryTime;
        return $this;
    }

    public function getDeliveryPlace(): ?string
    {
        // в старых подписках хранился ID адреса
        if($this->deliveryPlace > 0 && strcasecmp(intval($this->deliveryPlace), $this->deliveryPlace) === 0){
            try {
                /** @var AddressService $addressService */
                $addressService = Application::getInstance()->getContainer()->get('address.service');
                $personalAddress = $addressService->getById($this->deliveryPlace);
                $this->deliveryPlace = $personalAddress->getFullAddress();
            } catch (\Exception $e) {
                // если адреса уже нет, принудительно установим 0, чтобы деактивировать подписку
                $this->deliveryPlace = '0';
            }

        }
        return $this->deliveryPlace;
    }

    /**
     * @param string $deliveryPlace
     * @return OrderSubscribe
     */
    public function setDeliveryPlace(string $deliveryPlace): OrderSubscribe
    {
        $this->deliveryPlace = $deliveryPlace;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocationId(): ?string
    {
        return $this->locationId;
    }

    /**
     * @param string $locationId
     * @return OrderSubscribe
     */
    public function setLocationId(string $locationId): OrderSubscribe
    {
        $this->locationId = $locationId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return OrderSubscribe
     */
    public function setActive(bool $active): OrderSubscribe
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     * @return OrderSubscribe
     */
    public function setOrderId(int $orderId): OrderSubscribe
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getNextDate(): ?DateTime
    {
        return $this->nextDate;
    }

    /**
     * @param DateTime $nextDate
     * @return OrderSubscribe
     */
    public function setNextDate(DateTime $nextDate): OrderSubscribe
    {
        $this->nextDate = $nextDate;
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
     * @return OrderSubscribe
     */
    public function setDateCreate(DateTime $dateCreate): OrderSubscribe
    {
        $this->dateCreate = $dateCreate;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateUpdate(): ?DateTime
    {
        return $this->dateUpdate;
    }

    /**
     * @param DateTime $dateUpdate
     * @return OrderSubscribe
     */
    public function setDateUpdate(DateTime $dateUpdate): OrderSubscribe
    {
        $this->dateUpdate = $dateUpdate;
        return $this;
    }

    /**
     * @param bool $payWithbonus
     * @return OrderSubscribe
     */
    public function setPayWithbonus(bool $payWithbonus): OrderSubscribe
    {
        $this->payWithbonus = $payWithbonus;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPayWithbonus(): bool
    {
        return $this->payWithbonus ?: false;
    }

    /**
     * @return null|DateTime
     */
    public function getLastCheck()
    {
        return $this->lastCheck ?? null;
    }

    /**
     * @param null|DateTime|string $lastCheckDate
     *
     * @return self
     */
    public function setLastCheck($lastCheckDate) : self
    {
        $this->lastCheck = $this->processDateTimeValue($lastCheckDate);

        return $this;
    }

    /**
     * @param $value
     * @return DateTime|null|string
     */
    protected function processDateTimeValue($value)
    {
        if (!($value instanceof DateTime)) {
            if ($value === '' || $value === false) {
                $value = '';
            } elseif (is_string($value) && $value !== '') {
                $value = new DateTime($value, 'd.m.Y H:i:s');
            } else {
                $value = null;
            }
        }

        return $value;
    }

    /**
     * @return OrderSubscribeService
     */
    protected function getOrderSubscribeService() : OrderSubscribeService
    {
        $orderSubscribeService = Application::getInstance()->getContainer()->get('order_subscribe.service');
        return $orderSubscribeService;
    }

    /**
     * @return OrderSubscribeService
     * @throws ApplicationCreateException
     */
    protected function getOrderSubscribeHistoryService() : OrderSubscribeHistoryService
    {
        $orderSubscribeHistoryService = Application::getInstance()->getContainer()->get('order_subscribe_history.service');
        return $orderSubscribeHistoryService;
    }

    /**
     * @return UserFieldEnumService
     * @throws ApplicationCreateException
     */
    protected function getUserFieldEnumService() : UserFieldEnumService
    {
        if (!$this->userFieldEnumService) {
            $appCont = Application::getInstance()->getContainer();
            $this->userFieldEnumService = $appCont->get('userfield_enum.service');
        }

        return $this->userFieldEnumService;
    }

    /**
     * @return Order
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws \Exception
     */
    public function getOrder()
    {
        if (!isset($this->order)) {
            $this->order = $this->getOrderSubscribeService()->getOrderById(
                $this->getOrderId()
            );
        }
        if (!$this->order) {
            throw new NotFoundException('Карточка заказа не найдена');
        }

        return $this->order;
    }

    /**
     * @return User
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws \Exception
     */
    public function getUser() : User
    {
        if (!isset($this->user)) {
            $this->user = null;
            $subscribeService = $this->getOrderSubscribeService();
            $userRepository = $subscribeService->getCurrentUserService()->getUserRepository();
            $this->user = $userRepository->find(
                $this->getUserId()
            );
        }
        if (!$this->user) {
            throw new NotFoundException('Пользователь не найден');
        }

        return $this->user;
    }

    /**
     * @return UserFieldEnumValue
     * @throws ApplicationCreateException
     * @throws \Exception
     */
    public function getDeliveryFrequencyEntity()
    {
        if (!isset($this->deliveryFrequencyEntity)) {
            $this->deliveryFrequencyEntity = $this->getUserFieldEnumService()->getEnumValueEntity(
                $this->getFrequency()
            );
        }

        return $this->deliveryFrequencyEntity;
    }

    /**
     * @return int
     */
    public function getCheckDays()
    {
        return $this->checkDays;
    }

    /**
     * @param $checkDays
     */
    public function setCheckDays(\DateTime $deliveryDate): self
    {
        $deliveryDate = clone $deliveryDate->setTime(0,0,0,0);
        $curDate = (new \DateTime())->setTime(0,0,0,0);
        $checkDays = (int)$deliveryDate->diff($curDate)->format('%d');
        // хотя бы один день для подстраховки
        if($checkDays == 0){
            $checkDays = 1;
        }
        $this->checkDays = $checkDays;
        return $this;
    }

    /**
     * @param DateTime $dateCheck
     * @return OrderSubscribe
     */
    public function setDateCheck(DateTime $dateCheck): OrderSubscribe
    {
        $this->dateCheck = $dateCheck;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCheck(): ?DateTime
    {
        return $this->dateCheck;
    }

    /**
     * @return OrderSubscribe
     * @throws \Bitrix\Main\ObjectException
     */
    public function countNextDate(): OrderSubscribe
    {
        $this->getOrderSubscribeService()->countNextDate($this);
        return $this;
    }

    /**
     * @return bool
     * @throws OrderSubscribeException
     */
    public function countDateCheck(): bool
    {
        $deliveryDate = $this->getNextDate();
        $checkDays = $this->getCheckDays();

        if(!$deliveryDate){
            throw new OrderSubscribeException(sprintf("Не установлена дата следущей доставки [id:%s, user_id: %s]", $this->getId(), $this->getUserId()));
        }
        if(!$checkDays || $checkDays <= 0){
            throw new OrderSubscribeException(sprintf("Не установлено поле \"Кол-во дней до заказа\" [id:%s, user_id: %s]", $this->getId(), $this->getUserId()));
        }

        $dateCheck = (clone $deliveryDate)->setTime(9,0,0)->add(sprintf("-%s days", $checkDays));
        $this->setDateCheck($dateCheck);
        return true;
    }

    /**
     * @param $value
     * @return Date|null|string
     */
    protected function processDateValue($value)
    {
        if (!($value instanceof Date)) {
            if (is_scalar($value) && $value !== '') {
                $value = new Date($value, 'd.m.Y');
            } elseif ($value === '' || $value === false) {
                $value = '';
            } else {
                $value = null;
            }
        }

        return $value;
    }

    /**
     * @param string $format
     * @return string
     */
    public function getDateStartFormatted(string $format = 'd.m.Y') : string
    {
        $date =  $this->getDateCreate();

        return $date ? $date->format($format) : '';
    }

    /**
     * @return int
     */
    public function getDateStartWeekday() : int
    {
        $dateStart = $this->getDateCreate();

        return $dateStart ? (int)$dateStart->format('N') : 0;
    }

    /**
     * @param bool $lower
     * @param string $case
     * @return string
     */
    public function getDateStartWeekdayRu(bool $lower = true, string $case = '') : string
    {
        $case = $case === '' ? DateHelper::NOMINATIVE : $case;
        $weekDay = $this->getDateStartWeekday();
        $result = $weekDay ? DateHelper::replaceRuDayOfWeek('#'.$weekDay.'#', $case) : '';

        return $lower ? ToLower($result) : $result;
    }

    /**
     * @return string
     */
    public function getDeliveryTimeNormalized() : string
    {
        $result = $this->getDeliveryTime();
        // &mdash;, &ndash;
        $result = str_replace(
            ['—', '–'],
            '-',
            $result
        );

        return $result;
    }

    /**
     * Преобразовывает значение вида "09:00-16:00" к виду: "с 9 до 16"
     *
     * @param bool $noBreak
     * @return string
     */
    public function getDeliveryTimeFormattedRu(bool $noBreak = false) : string
    {
        $result = $this->getDeliveryTimeNormalized();
        $pieces = explode('-', $result);
        if (count($pieces) === 2) {
            $from = trim($pieces[0]);
            $to = trim($pieces[1]);
            $timePieces = explode(':', $from);
            if (count($timePieces) === 2 && $timePieces[1] === '00') {
                $from = (int)$timePieces[0];
            }
            $timePieces = explode(':', $to);
            if (count($timePieces) === 2 && $timePieces[1] === '00') {
                $to = (int)$timePieces[0];
            }
            $result = 'с '.$from.' до '.$to;
            if ($noBreak) {
                $result = str_replace(' ', '&nbsp;', $result);
            }
        }

        return $result;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getItems()
    {
        return $this->getOrderSubscribeService()->getItemsBySubscribeId($this->getId());
    }

    /**
     * Возвращает полный адрес места доставки
     *
     * @return string|null
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     */
    public function getDeliveryPlaceAddress()
    {
        $result = null;

        /** @var OrderSubscribeService $orderSubscribeService */
        $orderSubscribeService = $this->getOrderSubscribeService();
        /** @var StoreService $storeService */
        $storeService = Application::getInstance()->getContainer()->get('store.service');
        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');

        if($orderSubscribeService->isDelivery($this)){
            $result = $this->getDeliveryPlace();
            if($result === "0"){
                $result = "Не удалось определить адрес";
            }
        } else {
            try {
                $store = $storeService->getStoreByXmlId($this->getDeliveryPlace());
            } catch (NotFoundStoreException $e) {
                // трудно проверять здесь DPD это или самовывоз,
                // если склад не найден - попробуем найти его в DPD
                $terminals = $deliveryService->getDpdTerminalsByLocation($this->getLocationId());
                $store = $terminals[$this->getDeliveryPlace()];
            }

            try {
                $result = $store->getAddress();
            } catch (\Throwable $e) {
                // ну давай хотя бы код магазина отбразим
                $result = $this->getDeliveryPlace();
            }
        }

        return $result;
    }

    /**
     * @return bool
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     */
    public function isDelivery()
    {
        return $this->getOrderSubscribeService()->isDelivery($this);
    }

    /**
     * Возвращает цену товаров в подписке
     *
     * @return float|int
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getPrice()
    {
        $sum = 0;
        $orderSubscribeItems = $this->getItems();
        $items = [];

        /** @var OrderSubscribeItem $orderSubscribeItem */
        foreach($orderSubscribeItems as $orderSubscribeItem){
            $items[$orderSubscribeItem->getOfferId()] = [
                'ID' => $orderSubscribeItem->getOfferId(),
                'QUANTITY' => $orderSubscribeItem->getQuantity(),
            ];
        }

        $offerCollection = (new OfferQuery())->withFilter(['ID' => array_column($items, 'ID')])->exec();
        /** @var Offer $offer */
        foreach($offerCollection as $offer){
            $sum += $offer->getSubscribePrice() * $items[$offer->getId()]['QUANTITY'];
        }

        return $sum;
    }

    /**
     * Возвращает цену доставки
     *
     * @return float|int
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Sale\UserMessageException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\PersonalBundle\Exception\OrderSubscribeException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    public function getDeliveryPrice()
    {
        $price = 0;

        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');

        $delivery = $deliveryService->getDeliveryById($this->getDeliveryId());
        $deliveryCode = $deliveryService->getDeliveryCodeById($delivery['ID']);
        $basket = $this->getOrderSubscribeService()->getBasketBySubscribeId($this->getId());

        $calcResults = $deliveryService->getByBasket($basket, $this->getLocationId(), [$deliveryCode]);
        $calcResult = current($calcResults);
        if($calcResult)
            $price = $calcResult->getPrice();

        return $price;
    }

    /**
     * @return DateTime|null
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ObjectException
     */
    public function getPreviousDate()
    {
        return $this->getOrderSubscribeService()->getPreviousDate($this);
    }

    /**
     * @return array|false
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getNearestDelivery()
    {
        return $this->getOrderSubscribeHistoryService()->getNearestDelivery($this);
    }


}