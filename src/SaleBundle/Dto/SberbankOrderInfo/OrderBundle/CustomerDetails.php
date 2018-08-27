<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\SberbankOrderInfo\OrderBundle;

use JMS\Serializer\Annotation as Serializer;

class CustomerDetails
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("email")
     * @Serializer\Type("string")
     */
    protected $email;

    /**
     * @var string
     *
     * @Serializer\SerializedName("phone")
     * @Serializer\Type("string")
     */
    protected $phone;

    /**
     * @var string
     *
     * @Serializer\SerializedName("contact")
     * @Serializer\Type("string")
     */
    protected $contact;

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return CustomerDetails
     */
    public function setEmail(string $email): CustomerDetails
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return CustomerDetails
     */
    public function setPhone(string $phone): CustomerDetails
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getContact(): string
    {
        return $this->contact;
    }

    /**
     * @param string $contact
     * @return CustomerDetails
     */
    public function setContact(string $contact): CustomerDetails
    {
        $this->contact = $contact;

        return $this;
    }
}
