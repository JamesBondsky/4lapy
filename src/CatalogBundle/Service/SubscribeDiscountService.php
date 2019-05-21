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
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\LocationBundle\LocationService;

class SubscribeDiscountService
{
    // код скидки для всех
    const ALL = 'ALL';

    private $multipleProps = ['BRAND', 'REGION_CODE'];
    private $discounts;
    private $regionCode;


    /**
     * Заполняет массив всеми скидками
     *
     * @return array
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getDiscounts(): array
    {
        if (null === $this->discounts) {
            $res = ElementTable::getList([
                'select' => [
                    'ID',
                ],
                'filter' => [
                    'ACTIVE' => 'Y',
                    'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::SUBSCRIBE_PRICES),
                ],
            ]);

            $discountIds = [];
            while ($row = $res->fetch()) {
                $discountIds[] = $row['ID'];
            }

            if (empty($discountIds)) {
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
            while ($row = $res->fetch()) {
                $discountProperties[$row['CODE']] = $row;
                $discountPropertyIds[$row['CODE']] = $row['ID'];
            }

            $brandPropertyEntity = Base::compileEntity(
                'BRAND_PROPERTY',
                [
                    'ID' => ['data_type' => 'integer'],
                    'IBLOCK_PROPERTY_ID' => ['data_type' => 'integer'],
                    'IBLOCK_ELEMENT_ID' => ['data_type' => 'integer'],
                    'VALUE' => ['data_type' => 'string'],
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

            while ($row = $rsProps->fetch()) {
                $code = array_search($row['IBLOCK_PROPERTY_ID'], $discountPropertyIds);
                if (in_array($code, $this->multipleProps)) {
                    $discounts[$row['IBLOCK_ELEMENT_ID']][$code][] = $row['VALUE'];
                } else {
                    $discounts[$row['IBLOCK_ELEMENT_ID']][$code] = $row['VALUE'];
                }
            }

            $discountsByRegion = [];
            foreach ($discounts as $discount) {
                foreach ($discount['REGION_CODE'] as $regionCode) {
                    $discountsByRegion[$regionCode][] = $discount;
                }
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

    /**
     * @param $discount
     * @return int
     */
    public function getDiscountValue($discount)
    {
        return (int)$discount['PERCENT'];
    }

    /**
     * @param $region
     * @return array
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getDiscountsByRegion($region)
    {
        return $this->getDiscounts()[$region] ?: [];
    }

    /**
     * @return array
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getBaseDiscounts()
    {
        return $this->getDiscounts()[self::ALL] ?: [];
    }


    /**
     * Находит наиболее подходящую скидку для торгового предложения
     *
     * @param $discounts
     * @param Offer $offer
     * @return null
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function getBestDiscount(Offer $offer, $regionCode = false)
    {
        $discountBest = null;
        $discountWithoutBrand = null;
        if (!$regionCode) {
            $regionCode = $this->getRegion();
        }

        $discountsByRegion = $this->getDiscountsByRegion($regionCode);
        if (!empty($discountsByRegion)) {
            foreach ($discountsByRegion as $discount) {
                if ($this->canBeApplied($discount, $offer, $regionCode) && ($discountBest['PERCENT'] > $discount['PERCENT'] || !$discountBest)) {
                    $discountBest = $discount;
                } elseif (empty($discount['BRAND']) && (!$discountWithoutBrand || $discount['PERCENT'] > $discountWithoutBrand['PERCENT'])) {
                    $discountWithoutBrand = $discount;
                }
            }
            if (!$discountBest && $discountWithoutBrand) {
                $discountBest = $discountWithoutBrand;
            }
        }
        if(!$discountBest){
            $discountsBase = $this->getBaseDiscounts($regionCode);
            if(!empty($discountsBase)){
                foreach ($discountsBase as $discount) {
                    if ($this->canBeApplied($discount, $offer, $regionCode) && ($discountBest['PERCENT'] > $discount['PERCENT'] || !$discountBest)) {
                        $discountBest = $discount;
                    } elseif (empty($discount['BRAND']) && (!$discountWithoutBrand || $discount['PERCENT'] > $discountWithoutBrand['PERCENT'])) {
                        $discountWithoutBrand = $discount;
                    }
                }
                if (!$discountBest && $discountWithoutBrand) {
                    $discountBest = $discountWithoutBrand;
                }
            }
        }

        return $discountBest;
    }

    /**
     * @return bool
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function canBeApplied($discount, Offer $offer, $region)
    {
        return in_array($offer->getProduct()->getBrandId(), $discount['BRAND'])
            && (in_array($region, $discount['REGION_CODE']) || (count($discount['REGION_CODE']) == 1 && $discount['REGION_CODE'][0] == self::ALL));
    }

    /**
     * @return string
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function getRegion()
    {
        if (null == $this->regionCode) {
            /** @var LocationService $locationService */
            $locationService = Application::getInstance()->getContainer()->get('location.service');
            $this->regionCode = $locationService->getCurrentRegionCode();
        }
        return $this->regionCode;
    }

}