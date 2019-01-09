<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Dto\Object;


use FourPaws\App\Application;
use FourPaws\AppBundle\Entity\UserFieldEnumValue;
use FourPaws\AppBundle\Traits\UserFieldEnumTrait;
use FourPaws\UserBundle\Repository\UserRepository;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use FourPaws\UserBundle\Entity\User;

class PushMessage
{
    use UserFieldEnumTrait;

    /**
     * @var int
     * @Serializer\SerializedName("ID")
     * @Serializer\Type("int")
     * @Assert\NotBlank()
     */
    protected $id;

    /**
     * @var bool
     * @Serializer\SerializedName("UF_ACTIVE")
     * @Serializer\Type("bool")
     */
    protected $active;

    /**
     * @var string
     * @Serializer\SerializedName("UF_MESSAGE")
     * @Serializer\Type("string")
     */
    protected $message;

    /**
     * @var \DateTime
     * @Serializer\SerializedName("UF_START_SEND")
     * @Serializer\Type("DateTime<'d.m.Y H:i:s'>")
     */
    protected $startSend;

    /**
     * @var int
     * @Serializer\SerializedName("UF_TYPE")
     * @Serializer\Type("int")
     */
    protected $typeId;

    /**
     * @var int
     * @Serializer\SerializedName("UF_EVENT_ID")
     * @Serializer\Type("int")
     */
    protected $eventId;

    /**
     * @var int[]
     * @Serializer\SerializedName("UF_GROUPS")
     * @Serializer\Type("array<int>")
     */
    protected $groupIds;

    /**
     * @var int[]
     * @Serializer\SerializedName("UF_USERS")
     * @Serializer\Type("array<int>")
     */
    protected $userIds;

    /**
     * @var int
     * @Serializer\SerializedName("UF_FILE")
     * @Serializer\Type("int")
     */
    protected $fileId;

    /**
     * @var int
     * @Serializer\SerializedName("UF_PLATFORM")
     * @Serializer\Type("int")
     */
    protected $platformId;

    /** @var UserFieldEnumValue $typeEntity */
    private $typeEntity;

    /** @var UserFieldEnumValue[] $groupEntity */
    private $groupEntity;

    /** @var User[] $users */
    private $users;

    /** @var string $filePath */
    private $filePath;

    /** @var UserFieldEnumValue $platformEntity */
    private $platformEntity;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return PushMessage
     */
    public function setActive(bool $active): PushMessage
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return PushMessage
     */
    public function setMessage(string $message): PushMessage
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartSend(): \DateTime
    {
        return $this->startSend;
    }

    /**
     * @param \DateTime $startSend
     * @return PushMessage
     */
    public function setStartSend(\DateTime $startSend): PushMessage
    {
        $this->startSend = $startSend;
        return $this;
    }

    /**
     * @return int
     */
    public function getTypeId(): int
    {
        return $this->typeId;
    }

    /**
     * @param int $typeId
     * @return PushMessage
     */
    public function setTypeId(int $typeId): PushMessage
    {
        $this->typeId = $typeId;
        return $this;
    }

    /**
     * @return UserFieldEnumValue
     */
    public function getTypeEntity()
    {
        if (!isset($this->typeEntity)) {
            $this->typeEntity = $this->getUserFieldEnumService()->getEnumValueEntity(
                $this->getTypeId()
            );
        }

        return $this->typeEntity;
    }

    /**
     * @return int
     */
    public function getEventId(): int
    {
        return $this->eventId;
    }

    /**
     * @param int $eventId
     * @return PushMessage
     */
    public function setEventId(int $eventId): PushMessage
    {
        $this->eventId = $eventId;
        return $this;
    }

    /**
     * @return int[]
     */
    public function getGroupIds(): array
    {
        return $this->groupIds;
    }

    /**
     * @param int[] $groupIds
     * @return PushMessage
     */
    public function setGroupIds(array $groupIds): PushMessage
    {
        $this->groupIds = $groupIds;
        return $this;
    }

    /**
     * @return UserFieldEnumValue[]
     */
    public function getGroupEntity(): array
    {
        $this->groupEntity = [];
        if (empty($this->groupEntity)) {
            foreach ($this->getGroupIds() as $groupId) {
                $this->groupEntity[] = $this->getUserFieldEnumService()->getEnumValueEntity(
                    $groupId
                );

            }
        }

        return $this->groupEntity;
    }

    /**
     * @return int[]
     */
    public function getUserIds(): array
    {
        return $this->userIds;
    }

    /**
     * @param array $userIds
     * @return PushMessage
     */
    public function setUserIds(array $userIds): PushMessage
    {
        $this->userIds = $userIds;
        return $this;
    }

    /**
     * @return User[]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getUsers(): array
    {
        if (empty($this->users)) {
            /** @var UserRepository $userRepository */
            $userRepository = Application::getInstance()->getContainer()->get('FourPaws\UserBundle\Repository\UserRepository');
            $this->users = $userRepository->findBy([
                '=ID' => $this->getUserIds()
            ]);
        }
        return $this->users;
    }

    /**
     * @return int
     */
    public function getFileId(): int
    {
        return $this->fileId;
    }

    /**
     * @param int $fileId
     * @return PushMessage
     */
    public function setFileId(int $fileId): PushMessage
    {
        $this->fileId = $fileId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        if (empty($this->filePath)) {
            $this->filePath = \CFile::getPath($this->getFileId());
        }
        return $this->filePath;
    }

    /**
     * @return int
     */
    public function getPlatformId(): int
    {
        return $this->platformId;
    }

    /**
     * @param int $platformId
     * @return PushMessage
     */
    public function setPlatformId(int $platformId): PushMessage
    {
        $this->platformId = $platformId;
        return $this;
    }

    /**
     * @return UserFieldEnumValue
     */
    public function getPlatformEntity()
    {
        if (!isset($this->platformEntity)) {
            $this->platformEntity = $this->getUserFieldEnumService()->getEnumValueEntity(
                $this->getPlatformId()
            );
        }

        return $this->platformEntity;
    }

}