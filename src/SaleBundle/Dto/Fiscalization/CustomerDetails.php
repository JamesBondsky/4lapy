<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\Fiscalization;

class CustomerDetails
{
    /**
     * @var string
     */
    protected $contact;

    /**
     * @var string
     */
    protected $email;

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
}