<?php

namespace LinguaLeo\ExpertSender\Request;

use BadMethodCallException;
use LinguaLeo\ExpertSender\Entities\Property;
use LinguaLeo\ExpertSender\ExpertSenderEnum;

/**
 * Represents adding subscriber to list request attributes.
 *
 * https://sites.google.com/a/expertsender.com/api-documentation/methods/subscribers/add-subscriber#TOC-Request-data-format
 */
class AddUserToList
{
    /**
     * @var bool
     */
    private $frozen = false;

    /**
     * @var int|null
     */
    private $listId;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $email;

    /**
     * @var string|null
     */
    private $firstName;

    /**
     * @var string|null
     */
    private $lastName;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $ip;

    /**
     * @var string|null
     */
    private $trackingCode;

    /**
     * @var string|null
     */
    private $vendor;

    /**
     * @var bool
     */
    private $force = false;

    /**
     * @var string
     */
    private $mode = ExpertSenderEnum::MODE_ADD_AND_UPDATE;

    /**
     * @var array
     */
    private $properties = [];

    /**
     * @var string|null
     */
    private $phone;

    /**
     * @var string|null
     */
    private $customSubscriberId;

    /**
     * @return bool
     */
    public function isValid()
    {
        return null !== $this->email && null !== $this->listId;
    }

    /**
     * @return bool
     */
    public function isFrozen()
    {
        return $this->frozen;
    }

    /**
     * @throws BadMethodCallException
     *
     * @return AddUserToList
     */
    public function freeze()
    {
        if (!$this->isValid()) {
            throw new BadMethodCallException('AddUserToListRequest cannot be frozen when is invalid.');
        }

        $this->frozen = true;

        return $this;
    }

    /**
     * @param int|null $listId
     *
     * @throws BadMethodCallException
     *
     * @return AddUserToList
     */
    public function setListId($listId = null)
    {
        $this->exceptionIfFrozen();

        $this->listId = null === $listId ? null : (int) $listId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * @param int|null $id
     *
     * @throws BadMethodCallException
     *
     * @return AddUserToList
     */
    public function setId($id = null)
    {
        $this->exceptionIfFrozen();

        $this->id = null === $id ? null : (int) $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|null $email
     *
     * @throws BadMethodCallException
     *
     * @return AddUserToList
     */
    public function setEmail($email = null)
    {
        $this->exceptionIfFrozen();

        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string|null $firstName
     *
     * @throws BadMethodCallException
     *
     * @return AddUserToList
     */
    public function setFirstName($firstName = null)
    {
        $this->exceptionIfFrozen();

        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string|null $lastName
     *
     * @throws BadMethodCallException
     *
     * @return AddUserToList
     */
    public function setLastName($lastName = null)
    {
        $this->exceptionIfFrozen();

        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string|null $name
     *
     * @throws BadMethodCallException
     *
     * @return AddUserToList
     */
    public function setName($name = null)
    {
        $this->exceptionIfFrozen();

        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $ip
     *
     * @throws BadMethodCallException
     *
     * @return AddUserToList
     */
    public function setIp($ip = null)
    {
        $this->exceptionIfFrozen();

        $this->ip = $ip;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string|null $trackingCode
     *
     * @throws BadMethodCallException
     * @throws \InvalidArgumentException
     *
     * @return AddUserToList
     */
    public function setTrackingCode($trackingCode = null)
    {
        $this->exceptionIfFrozen();

        if (strlen($trackingCode) > 20) {
            throw new \InvalidArgumentException('Tracking code is too long, max is 20 characters');
        }

        $this->trackingCode = $trackingCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTrackingCode()
    {
        return $this->trackingCode;
    }

    /**
     * @param string|null $vendor
     *
     * @throws BadMethodCallException
     *
     * @return AddUserToList
     */
    public function setVendor($vendor = null)
    {
        $this->exceptionIfFrozen();

        $this->vendor = $vendor;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param bool $force
     *
     * @throws BadMethodCallException
     * @throws \InvalidArgumentException
     *
     * @return AddUserToList
     */
    public function setForce($force)
    {
        $this->exceptionIfFrozen();

        if (!is_bool($force)) {
            throw new \InvalidArgumentException();
        }

        $this->force = $force;

        return $this;
    }

    /**
     * @return bool
     */
    public function getForce()
    {
        return $this->force;
    }

    /**
     * @param string $mode
     *
     * @throws BadMethodCallException
     * @throws \InvalidArgumentException
     *
     * @return AddUserToList
     */
    public function setMode($mode)
    {
        $this->exceptionIfFrozen();

        if (!in_array($mode, ExpertSenderEnum::getModes(), true)) {
            throw new \InvalidArgumentException('Invalid mode: '.$mode);
        }

        $this->mode = $mode;

        return $this;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string|null $phone
     *
     * @throws BadMethodCallException
     *
     * @return AddUserToList
     */
    public function setPhone($phone = null)
    {
        $this->exceptionIfFrozen();
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string|null $customSubscriberId
     *
     * @throws BadMethodCallException
     *
     * @return AddUserToList
     */
    public function setCustomSubscriberId($customSubscriberId = null)
    {
        $this->exceptionIfFrozen();
        $this->customSubscriberId = $customSubscriberId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCustomSubscriberId()
    {
        return $this->customSubscriberId;
    }

    /**
     * @param Property $property
     *
     * @throws BadMethodCallException
     *
     * @return AddUserToList
     */
    public function addProperty(Property $property)
    {
        $this->exceptionIfFrozen();

        $this->properties[] = $property;

        return $this;
    }

    /**
     * @param array $properties
     *
     * @throws BadMethodCallException
     *
     * @return AddUserToList
     */
    public function setProperties(array $properties)
    {
        $this->exceptionIfFrozen();

        $this->properties = [];

        foreach ($properties as $property) {
            $this->addProperty($property);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @throws BadMethodCallException
     */
    private function exceptionIfFrozen()
    {
        if ($this->frozen) {
            throw new BadMethodCallException('Attributes cannot be set when AddUserToListRequest is frozen.');
        }
    }
}
