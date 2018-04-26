<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\StoreBundle\Entity;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class ScheduleResult extends Base
{
    /**
     * @var int
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("ID")
     * @Serializer\Groups(groups={"read","update","delete"})
     * @Assert\NotBlank(groups={"read","update","delete"})
     * @Assert\GreaterThanOrEqual(value="1",groups={"read","update","delete"})
     * @Assert\Blank(groups={"create"})
     */
    protected $id;

    /**
     * @var Store
     */
    protected $receiver;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_RECEIVER")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    protected $receiverCode;

    /**
     * @var Store
     */
    protected $sender;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("UF_SENDER")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    protected $senderCode;

    /**
     * @var array
     * @Serializer\Type("array_or_false<string>")
     * @Serializer\SerializedName("UF_ROUTE")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    protected $routeCodes;

    /**
     * @var StoreCollection
     */
    protected $route;

    /**
     * @var int
     * @Serializer\Type("int")
     * @Serializer\SerializedName("UF_DAYS")
     * @Serializer\Groups(groups={"create", "read","update","delete"})
     * @Assert\NotBlank(groups={"create", "read","update","delete"})
     */
    protected $days;

    /**
     * @var StoreService
     */
    protected $storeService;

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
     * @return ScheduleResult
     */
    public function setId(int $id): ScheduleResult
    {
        $this->id = $id;
        return $this;
    }

    /**
     * ScheduleResult constructor.
     * @throws ApplicationCreateException
     */
    public function __construct()
    {
        $this->storeService = Application::getInstance()->getContainer()->get('store.service');
    }

    /**
     * @throws NotFoundException
     * @return Store
     */
    public function getReceiver(): Store
    {
        if (null === $this->receiver) {
            $this->receiver = $this->storeService->getStoreByXmlId($this->getSenderCode());
        }

        return $this->receiver;
    }

    /**
     * @param Store $receiver
     *
     * @return ScheduleResult
     */
    public function setReceiver(Store $receiver): ScheduleResult
    {
        $this->receiver = $receiver;
        $this->receiverCode = $receiver->getXmlId();

        return $this;
    }

    /**
     * @return string
     */
    public function getReceiverCode(): string
    {
        return $this->receiverCode;
    }

    /**
     * @param string $receiverCode
     *
     * @return ScheduleResult
     */
    public function setReceiverCode(string $receiverCode): ScheduleResult
    {
        $this->receiverCode = $receiverCode;
        if ($this->receiver && $this->receiver->getXmlId() !== $receiverCode) {
            $this->receiver = null;
        }

        return $this;
    }

    /**
     * @throws NotFoundException
     * @return Store
     */
    public function getSender(): Store
    {
        if (null === $this->sender) {
            $this->sender = $this->storeService->getStoreByXmlId($this->getSenderCode());
        }

        return $this->sender;
    }

    /**
     * @param Store $sender
     *
     * @return ScheduleResult
     */
    public function setSender(Store $sender): ScheduleResult
    {
        $this->sender = $sender;
        $this->senderCode = $sender->getXmlId();

        return $this;
    }

    /**
     * @return string
     */
    public function getSenderCode(): string
    {
        return $this->senderCode;
    }

    /**
     * @param string $senderCode
     *
     * @return ScheduleResult
     */
    public function setSenderCode(string $senderCode): ScheduleResult
    {
        $this->senderCode = $senderCode;
        if ($this->sender && $this->sender->getXmlId() !== $senderCode) {
            $this->sender = null;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRouteCodes(): array
    {
        return $this->routeCodes;
    }

    /**
     * @param array $routeCodes
     *
     * @return ScheduleResult
     */
    public function setRouteCodes(array $routeCodes): ScheduleResult
    {
        $this->routeCodes = $routeCodes;
        $this->route = null;

        return $this;
    }

    /**
     * @throws NotFoundException
     * @return StoreCollection
     */
    public function getRoute(): StoreCollection
    {
        if (null === $this->route) {
            $this->route = new StoreCollection();
            foreach ($this->routeCodes as $xmlId) {
                $this->route[$xmlId] = $this->storeService->getStoreByXmlId($xmlId);
            }
        }

        return $this->route;
    }

    /**
     * @param StoreCollection $route
     *
     * @return ScheduleResult
     */
    public function setRoute(StoreCollection $route): ScheduleResult
    {
        $this->route = $route;
        $this->routeCodes = array_keys($route->toArray());

        return $this;
    }

    /**
     * @return int
     */
    public function getDays(): int
    {
        return $this->days;
    }

    /**
     * @param int $days
     *
     * @return ScheduleResult
     */
    public function setDays(int $days): ScheduleResult
    {
        $this->days = $days;
        return $this;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            $this->id,
            $this->receiverCode,
            $this->senderCode,
            $this->routeCodes,
            $this->days
        ]);
    }

    /**
     * @return Store
     * @throws NotFoundException
     */
    public function getLastSender(): Store
    {
        $keys = array_reverse($this->getRoute()->getKeys());
        return $this->getRoute()->get($keys[1]);
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @param string $serialized
     *
     * @throws ApplicationCreateException
     */
    public function unserialize($serialized): void
    {
        [
            $this->id,
            $this->receiverCode,
            $this->senderCode,
            $this->routeCodes,
            $this->days
        ] = \unserialize($serialized, ['allowed_classes' => true]);

        $this->__construct();
    }
}
