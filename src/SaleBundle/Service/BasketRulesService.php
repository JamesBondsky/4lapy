<?php
/**
 * Created by PhpStorm.
 * Date: 16.05.2018
 * Time: 20:24
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Service;

use FourPaws\SapBundle\Model\BasketRule;
use FourPaws\SapBundle\Repository\BasketRulesRepository;


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
}