<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 27.03.2019
 * Time: 13:08
 */

namespace FourPaws\CatalogBundle\Service;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Entity\Base;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class SubscribeDiscountService
{

    private $discounts;

    /**
     * @return array
     */
    public function getDiscounts(): array
    {
        if (null === $this->discounts) {
            $res = ElementTable::getList([
                'select' => [
                    'ID',
                    //'REGION' => 'PROPERTIES.REGION_CODE',
                    //'PERCENT' => 'PROPERTIES.PERCENT',
                ],
                'filter' => [
                    'ACTIVE' => 'Y',
                    'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::SUBSCRIBE_PRICES),
                ],
                'cache' => [
                    'ttl' => 3600 * 24 * 365
                ],
                /*'runtime' => [
                    'PROPERTIES' => [
                        'data_type' => '\Bitrix\Iblock\ElementPropertyTable',
                        'reference' => ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID'],
                        'join_type' => 'left',
                    ]
                ]*/
            ]);

            $discountIds = [];
            while ($row = $res->fetch()) {
                $discountIds[] = $row['ID'];
            }

            if(empty($discountIds)){
                $this->setDiscounts([]);
                return $this->discounts;
            }

            $res = PropertyTable::getList([
                'select' => [
                    'ID',
                    'NAME',
                    'CODE'
                ],
                'filter' => [
                    'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::SUBSCRIBE_PRICES),
                    'CODE' => ['BRAND', 'REGION_CODE', 'PERCENT'],
                ]
            ]);

            $discountProperties = [];
            $discountPropertyIds = [];
            while($row = $res->fetch()){
                $discountProperties[$row['CODE']] = $row;
                $discountPropertyIds[$row['CODE']] = $row['ID'];
            }

            $brandPropertyEntity = Base::compileEntity(
                'BRAND_PROPERTY',
                [
                    'ID' => ['data_type' => 'integer'],
                    'IBLOCK_PROPERTY_ID' => ['data_type' => 'integer'],
                    'IBLOCK_ELEMENT_ID'  => ['data_type' => 'integer'],
                    'VALUE'  => ['data_type' => 'string'],
                ],
                ['table_name' => 'b_iblock_element_property']
            );

            $rsProps = $brandPropertyEntity->getDataClass()::getList([
                'filter' => [
                    'IBLOCK_ELEMENT_ID' => $discountIds,
                    'IBLOCK_PROPERTY_ID' => $discountPropertyIds,
                ],
                'select' => [
                    '*',
                ],
            ]);

            while($row = $rsProps->fetch()){
                $code = array_search($row['IBLOCK_PROPERTY_ID'], $discountPropertyIds);
                if($code == 'BRAND'){
                    $discounts[$row['IBLOCK_ELEMENT_ID']][$code][] = $row['VALUE'];
                }
                else{
                    $discounts[$row['IBLOCK_ELEMENT_ID']][$code] = $row['VALUE'];
                }
            }

            $discountsByRegion = [];
            foreach ($discounts as $discount){
                $discountsByRegion[$discount['REGION_CODE']][] = $discount;
            }
            unset($discounts);

            $this->setDiscounts($discountsByRegion);
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

    public function getDiscountsByRegion($region)
    {
        return $this->getDiscounts()[$region] ?: [];
    }
}