<?php
/**
 * Created by PhpStorm.
 * Date: 16.05.2018
 * Time: 20:24
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\SapBundle\Model\BasketRule;
use FourPaws\SapBundle\Repository\BasketRulesRepository;
use WebArch\BitrixCache\BitrixCache;


/**
 * Class BasketRulesService
 * @package FourPaws\SaleBundle\Service
 */
class BasketRulesService
{
    const SIMPLE_DISCOUNT_CODE = 'SimpleDiscountPreset';
    const PROMO_PRICE_DISCOUNT_CODE = 'PromoPriceDiscountPreset';

    public static $simpleDiscounts = [self::SIMPLE_DISCOUNT_CODE, self::PROMO_PRICE_DISCOUNT_CODE];

    /**
     * @var BasketRulesRepository
     */
    private $basketRulesRepository;

    /**
     * BasketRulesService constructor.
     *
     * @param BasketRulesRepository $basketRulesRepository
     */
    public function __construct(BasketRulesRepository $basketRulesRepository)
    {
        $this->basketRulesRepository = $basketRulesRepository;
    }

    /**
     * пересохраняет все скидки ./bin/symfony_console f:s:d:r
     *
     * @throws \FourPaws\SapBundle\Exception\InvalidArgumentException
     * @throws \FourPaws\SapBundle\Exception\BitrixEntityProxyException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function resaveAll(): void
    {
        foreach($this->basketRulesRepository->getAll() as $basketRule) {
            $this->basketRulesRepository->update($basketRule);
        }
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public function getSimpleDiscounts(): array
    {
        return $this->basketRulesRepository->findByXmlId(self::$simpleDiscounts);
    }

    /**
     * @param BasketRule $basketRule
     * @return bool
     */
    public function isSimpleDiscount(BasketRule $basketRule): bool
    {
        return $basketRule->getXmlId() == self::SIMPLE_DISCOUNT_CODE;
    }

    /**
     * @param BasketRule $basketRule
     * @return bool
     */
    public function isPromoPriceDiscount(BasketRule $basketRule): bool
    {
        return $basketRule->getXmlId() == self::PROMO_PRICE_DISCOUNT_CODE;
    }

    /**
     * @return array
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    public function getRegionalDiscounts()
    {
        $regionDiscounts = (new BitrixCache())
            ->withIblockTag(IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES))
            ->withTime(3600*24*356)
            ->resultOf(function () {
                $arDiscounts = [];
                $dbres = \CIBlockElement::GetList([],
                    [
                        'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES),
                        'ACTIVE' => 'Y',
                        'ACTIVE_DATE' => 'Y',
                        '!PROPERTY_REGION' => false
                    ],
                    false,
                    false,
                    ['ID', 'XML_ID', 'NAME', 'PROPERTY_REGION', 'PROPERTY_BASKET_RULES']
                );
                while($row = $dbres->fetch()){
                    foreach ($row['PROPERTY_BASKET_RULES_VALUE'] as $discountId){
                        $arDiscounts[$discountId] = $row['PROPERTY_REGION_VALUE'];
                    }
                }
                return $arDiscounts;
            });
        return $regionDiscounts;
    }
}