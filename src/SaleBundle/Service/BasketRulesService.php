<?php
/**
 * Created by PhpStorm.
 * Date: 16.05.2018
 * Time: 20:24
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Service;

use FourPaws\SapBundle\Repository\BasketRulesRepository;


/**
 * Class BasketRulesService
 * @package FourPaws\SaleBundle\Service
 */
class BasketRulesService
{
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
}