<?php

namespace FourPaws\External\Manzana\Dto;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class ChequePosition
 *
 * @package FourPaws\External\Manzana\Dto
 *
 * @Serializer\XmlRoot("Item")
 */
class ChequePosition
{
    /**
     * Идентификатор позиции чека в кассовой системе
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("cheque_item_id")
     *
     * @var integer
     */
    protected $chequeItemId = 0;
    
    /**
     * Идентификатор чека в кассовой системе
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("cheque_id")
     *
     * @var integer
     */
    protected $chequeId = 0;
    
    /**
     * Номер позиции чека в чеке
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("cheque_item_number")
     *
     * @var integer
     */
    protected $chequeItemNumber = 0;
    
    /**
     * Идентификатор товара
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("article_id")
     *
     * @var integer
     */
    protected $articleId = 0;
    
    /**
     * Цена единицы товара
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Price")
     *
     * @var float
     */
    protected $price = 0;
    
    /**
     * Число единиц товара
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("quantity")
     *
     * @var integer
     */
    protected $quantity = 0;
    
    /**
     * Стоимость товара. Вычисляется как произведение цены за единицу на количество товара.
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("Summ")
     *
     * @var float
     */
    protected $summ = 0;
    
    /**
     * Стоимость товара с учетом скидки
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("float")
     * @Serializer\SerializedName("summ_discounted")
     *
     * @var float
     */
    protected $summDiscounted = '';
    
    /**
     * Скидка, %
     *
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("Discount")
     *
     * @var float
     */
    protected $discount = '';
    
    /**
     * @return int
     */
    public function getChequeItemId() : int
    {
        return $this->chequeItemId;
    }
    
    /**
     * @param int $chequeItemId
     *
     * @return ChequePosition
     */
    public function setChequeItemId(int $chequeItemId) : ChequePosition
    {
        $this->chequeItemId = $chequeItemId;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getChequeId() : int
    {
        return $this->chequeId;
    }
    
    /**
     * @param int $chequeId
     *
     * @return ChequePosition
     */
    public function setChequeId(int $chequeId) : ChequePosition
    {
        $this->chequeId = $chequeId;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getArticleId() : int
    {
        return $this->articleId;
    }
    
    /**
     * @param int $articleId
     *
     * @return ChequePosition
     */
    public function setArticleId(int $articleId) : ChequePosition
    {
        $this->articleId = $articleId;
        
        return $this;
    }
    
    /**
     * @return float
     */
    public function getPrice() : float
    {
        return $this->price;
    }
    
    /**
     * @param float $price
     *
     * @return ChequePosition
     */
    public function setPrice(float $price) : ChequePosition
    {
        $this->price = $price;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getQuantity() : int
    {
        return $this->quantity;
    }
    
    /**
     * @param int $quantity
     *
     * @return ChequePosition
     */
    public function setQuantity(int $quantity) : ChequePosition
    {
        $this->quantity = $quantity;
        
        return $this;
    }
    
    /**
     * @return float
     */
    public function getSumm() : float
    {
        return $this->summ;
    }
    
    /**
     * @param float $summ
     *
     * @return ChequePosition
     */
    public function setSumm(float $summ) : ChequePosition
    {
        $this->summ = $summ;
        
        return $this;
    }
    
    /**
     * @return float
     */
    public function getSummDiscounted() : float
    {
        return $this->summDiscounted;
    }
    
    /**
     * @param float $summDiscounted
     *
     * @return ChequePosition
     */
    public function setSummDiscounted(float $summDiscounted) : ChequePosition
    {
        $this->summDiscounted = $summDiscounted;
        
        return $this;
    }
    
    /**
     * @return float
     */
    public function getDiscount() : float
    {
        return $this->discount;
    }
    
    /**
     * @param float $discount
     *
     * @return ChequePosition
     */
    public function setDiscount(float $discount) : ChequePosition
    {
        $this->discount = $discount;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getChequeItemNumber() : int
    {
        return $this->chequeItemNumber;
    }
    
    /**
     * @param int $chequeItemNumber
     *
     * @return $this
     */
    public function setChequeItemNumber(int $chequeItemNumber)
    {
        $this->chequeItemNumber = $chequeItemNumber;
        
        return $this;
    }
}
