<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Model;

use Bitrix\Main\Type\DateTime;
use FourPaws\BitrixOrm\Model\CustomTable;

/**
 * Class User
 *
 * @param
 *
 * @package FourPaws\BitrixOrm\Model
 */
class ConfirmCode extends CustomTable
{
    /**
     * @var string $ID
     */
    protected $ID;
    
    /**
     * @var string $CODE
     */
    protected $CODE;
    
    /**
     * @var DateTime $DATE
     */
    protected $DATE;

    /**
     * @var string $TYPE
     */
    protected $TYPE;
    
    /**
     * @return DateTime
     */
    public function getDate() : DateTime
    {
        return $this->DATE;
    }
    
    /**
     *
     * @param DateTime $date
     *
     * @return ConfirmCode
     */
    public function withDate(DateTime $date) : ConfirmCode
    {
        $this->DATE = $date;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getCode() : string
    {
        return $this->CODE ?? '';
    }
    
    /**
     * @param string $code
     *
     * @return ConfirmCode
     */
    public function withCode(string $code) : ConfirmCode
    {
        $this->CODE = $code;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->ID ?? '';
    }
    
    /**
     * @param string $id
     *
     * @return ConfirmCode
     */
    public function withId(string $id) : ConfirmCode
    {
        $this->ID = $id;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->TYPE;
    }

    /**
     * @param string $type
     */
    public function withType(string $type)
    {
        $this->TYPE = $type;
    }
}
