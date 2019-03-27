<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 27.03.2019
 * Time: 13:08
 */

namespace FourPaws\CatalogBundle\Service;


use Bitrix\Iblock\ElementTable;

class SubscribeDiscountService
{

    private $discounts = [];

    /**
     * @return array
     */
    public function getDiscounts(): array
    {
        if(null === $this->discounts){
            $res = ElementTable::getList([
                'select' => [
                    'ID',
                    ''
                ],
                'filter' => [

                ],
                'cache' => [
                    'ttl' => 3600*24*365
                ],
                'runtime' => [
                    'PROPERTIES' => [
                        'data_type' => '\Bitrix\Iblock\ElementPropertyTable',
                        'reference' => ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID'],
                        'join_type' => 'left',
                    ]
                ]
            ]);
        }

        return $this->discounts;
    }

    /**
     * @param array $discounts
     */
    public function setDiscounts(array $discounts)
    {
        $this->discounts = $discounts;
        return $this;
    }
}