<?php

namespace FourPaws\PersonalBundle\Repository\Table;

use Bitrix\Main,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class BasketsDiscountOfferTable
 *
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> basketId int optional
 * <li> userId int optional
 * <li> date_insert datetime mandatory
 * <li> date_update datetime mandatory
 * <li> order_created int mandatory
 * </ul>
 *
 * @package Bitrix\Baskets
 **/
class BasketsDiscountOfferTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return '4lapy_baskets_discount_offer';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            'id'            => [
                'data_type'    => 'integer',
                'primary'      => true,
                'autocomplete' => true,
                'title'        => Loc::getMessage('DISCOUNT_OFFER_ENTITY_ID_FIELD'),
            ],
            'fUserId'       => [
                'data_type' => 'integer',
                'title'     => Loc::getMessage('DISCOUNT_OFFER_ENTITY_FUSERID_FIELD'),
            ],
            'userId'        => [
                'data_type' => 'integer',
                'title'     => Loc::getMessage('DISCOUNT_OFFER_ENTITY_USERID_FIELD'),
            ],
            'date_insert'   => [
                'data_type' => 'datetime',
                'required'  => true,
                'title'     => Loc::getMessage('DISCOUNT_OFFER_ENTITY_DATE_INSERT_FIELD'),
            ],
            'date_update'   => [
                'data_type' => 'datetime',
                'required'  => true,
                'title'     => Loc::getMessage('DISCOUNT_OFFER_ENTITY_DATE_UPDATE_FIELD'),
            ],
            'order_created' => [
                'data_type' => 'integer',
                'required'  => true,
                'title'     => Loc::getMessage('DISCOUNT_OFFER_ENTITY_ORDER_CREATED_FIELD'),
            ],
            'promoCode'     => [
                'data_type'  => 'string',
                'validation' => [__CLASS__, 'validatePromocode'],
                'title'      => Loc::getMessage('DISCOUNT_OFFER_ENTITY_PROMOCODE_FIELD'),
            ],
            'isFromMobile'  => [
                'data_type' => 'integer',
                'title'     => Loc::getMessage('DISCOUNT_OFFER_ENTITY_ISFROMMOBILE_FIELD'),
            ],
        ];
    }

    /**
     * Returns validators for promoCode field.
     *
     * @return array
     */
    public static function validatePromocode()
    {
        return [
            new Main\Entity\Validator\Length(null, 30),
        ];
    }
}
