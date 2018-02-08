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
}
