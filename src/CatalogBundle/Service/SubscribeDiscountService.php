<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 27.03.2019
 * Time: 13:08
 */

namespace FourPaws\CatalogBundle\Service;


use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\LocationBundle\LocationService;

class SubscribeDiscountService
{
    // код скидки для всех
    public const ALL = 'ALL';

    private $multipleProps = ['BRAND', 'REGION_CODE', 'SECTION'];
    private $discounts;
    private $regionCode;


    /**
     * Заполняет массив всеми скидками
     *
     * @return array
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getDiscounts(): array
    {
        if (null === $this->discounts) {
            $subscribeIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::SUBSCRIBE_PRICES);

            $res = ElementTable::getList([
                'select' => [
                    'ID',
                ],
                'filter' => [
                    'ACTIVE' => 'Y',
                    'IBLOCK_ID' => $subscribeIblockId,
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
                    'IBLOCK_ID' => $subscribeIblockId,
                    'CODE' => ['BRAND', 'REGION_CODE', 'PERCENT', 'SECTION'],
                ]
            ]);

            $discountPropertyIds = [];
            while ($row = $res->fetch()) {
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

            $discounts = [];
            while ($row = $rsProps->fetch()) {
                $code = array_search($row['IBLOCK_PROPERTY_ID'], $discountPropertyIds, false);
                if (in_array($code, $this->multipleProps, false)) {
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
     * @return SubscribeDiscountService
     */
    public function setDiscounts(array $discounts): SubscribeDiscountService
    {
        $this->discounts = $discounts;
        return $this;
    }

    /**
     * @param $discount
     * @return int
     */
    public function getDiscountValue($discount): int
    {
        return (int)$discount['PERCENT'];
    }

    /**
     * @param $region
     * @return array
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getDiscountsByRegion($region): array
    {
        return $this->getDiscounts()[$region] ?: [];
    }

    /**
     * @return array
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getBaseDiscounts(): array
    {
        return $this->getDiscounts()[self::ALL] ?: [];
    }

    /**
     * Находит наиболее подходящую скидку для торгового предложения
     *
     * @param Offer $offer
     * @param bool|string $regionCode
     * @return null
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws IblockNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getBestDiscount(Offer $offer, $regionCode = false)
    {
        $discountBest = $this->extractBaseDiscount($this->getDiscountsByRegion($regionCode), $offer, $regionCode);

        if (!$discountBest) {
            $discountBest = $this->extractBaseDiscount($this->getBaseDiscounts(), $offer, $regionCode);
        }

        return $discountBest;
    }

    /**
     * @param array $discounts
     * @param Offer $offer
     * @param bool|string $regionCode
     * @return mixed|null
     * @throws SystemException
     * @throws ApplicationCreateException
     */
    protected function extractBaseDiscount(array $discounts, Offer $offer, $regionCode = false)
    {
        $discountBest = null;
        $discountWithoutBrand = null;

        if (!$regionCode) {
            $regionCode = $this->getRegion();
        }

        if (!empty($discounts)) {
            foreach ($discounts as $discount) {
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

        return $discountBest;
    }

    /**
     * @param $discount
     * @param Offer $offer
     * @param $region
     * @return bool
     * @throws SystemException
     */
    protected function canBeApplied($discount, Offer $offer, $region): bool
    {
        if (!(in_array($region, $discount['REGION_CODE'], false) || ((count($discount['REGION_CODE']) === 1) && ($discount['REGION_CODE'][0] === self::ALL)))) {
            return false;
        }

        if (!in_array($offer->getProduct()->getBrandId(), $discount['BRAND'], false)) {
            return false;
        }

        if (!isset($discount['SECTION']) || empty($discount['SECTION'])) {
            return true;
        }

        foreach ($offer->getProduct()->getSectionsIdList() as $sectionId) {
            if (in_array($sectionId, $discount['SECTION'], false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     * @throws ApplicationCreateException
     */
    protected function getRegion(): string
    {
        if (null === $this->regionCode) {
            /** @var LocationService $locationService */
            $locationService = Application::getInstance()->getContainer()->get('location.service');
            $this->regionCode = $locationService->getCurrentRegionCode();
        }
        return $this->regionCode;
    }
}
