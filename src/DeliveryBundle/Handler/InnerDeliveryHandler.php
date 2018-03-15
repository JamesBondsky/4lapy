<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Handler;

use Bitrix\Main\Error;
use Bitrix\Sale\Shipment;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Collection\IntervalRuleCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResult;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Service\StoreService;

class InnerDeliveryHandler extends DeliveryHandlerBase
{
    protected $code = '4lapy_delivery';

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    public static function getClassTitle()
    {
        return 'Доставка "Четыре лапы"';
    }

    public static function getClassDescription()
    {
        return 'Обработчик собственной доставки "Четыре лапы"';
    }

    public function isCompatible(Shipment $shipment)
    {
        if (!parent::isCompatible($shipment)) {
            return false;
        }

        return true;
    }

    public function getIntervals(Shipment $shipment): IntervalCollection
    {
        $result = new IntervalCollection();

        $deliveryZone = $this->deliveryService->getDeliveryZoneCode($shipment);

        $config = $this->getConfig();
        $intervalConfig = $config['MAIN']['ITEMS']['INTERVALS']['VALUE'];
        foreach ($intervalConfig as $intervalGroup) {
            if ($intervalGroup['ZONE_CODE'] !== $deliveryZone) {
                continue;
            }

            $intervalGroup['RULES'];
            foreach ($intervalGroup['INTERVALS'] as $intervalIndex => $interval) {
                $ruleCollection = new IntervalRuleCollection();
                foreach ($interval['RULES'] as $type => $values) {
                    if (!isset($intervalGroup['RULES'][$type])) {
                        continue;
                    }

                    $ruleData = [];
                    foreach ($values as $i => $value) {
                        if (!isset($intervalGroup['RULES'][$type][$i])) {
                            continue;
                        }

                        $ruleValue = $intervalGroup['RULES'][$type][$i];
                        $ruleValue['VALUE'] = $value;
                        $ruleData[] = $ruleValue;
                    }

                    $ruleCollection = new IntervalRuleCollection(
                        array_merge(
                            $ruleCollection->toArray(),
                            $this->intervalService->createRules($type, $ruleData)->toArray()
                        )
                    );
                }

                $result->add(
                    (new Interval())->setFrom($interval['FROM'])
                        ->setTo($interval['TO'])
                        ->setRules($ruleCollection)
                );
            }
        }

        return $result;
    }

    /**
     * @param Shipment $shipment
     * @return \Bitrix\Sale\Delivery\CalculationResult|DeliveryResult
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    protected function calculateConcrete(Shipment $shipment)
    {
        $result = new DeliveryResult();

        if (!$zone = $this->deliveryService->getDeliveryZoneCode($shipment)) {
            $result->addError(new Error('Не указано местоположение доставки'));
        } else {
            $result->setDeliveryZone($zone);
        }

        $basket = $shipment->getParentOrder()->getBasket()->getOrderableItems();

        $deliveryZone = $this->deliveryService->getDeliveryZoneCode($shipment, false);
        $deliveryLocation = $this->deliveryService->getDeliveryLocation($shipment);
        if ($this->config['PRICES'][$deliveryZone]) {
            $result->setDeliveryPrice($this->config['PRICES'][$deliveryZone]);

            if (!empty($this->config['FREE_FROM'][$deliveryZone])) {
                $result->setFreeFrom((int)$this->config['FREE_FROM'][$deliveryZone]);
                if ($basket->getPrice() >= $this->config['FREE_FROM'][$deliveryZone]) {
                    $result->setDeliveryPrice(0);
                }
            }
        } else {
            $result->addError(new Error('Не задана стоимость доставки'));
        }
        $result->setIntervals($this->getIntervals($shipment));
        if (!$offers = static::getOffers($deliveryLocation, $basket)) {
            /**
             * Нужно для отображения списка доставок в хедере и на странице доставок
             */
            return $result;
        }

        switch ($this->deliveryService->getDeliveryZoneCode($shipment)) {
            case DeliveryService::ZONE_1:
                /**
                 * условие доставки в эту зону - наличие на складе
                 */
                $availableStores = $this->storeService->getByLocation($deliveryLocation, StoreService::TYPE_STORE);
                break;
            case DeliveryService::ZONE_2:
                /**
                 * условие доставки в эту зону - наличие в базовом магазине
                 */
                $stores = $this->storeService->getByLocation($deliveryLocation, StoreService::TYPE_ALL);
                $availableStores = $stores->getBaseShops();
                break;
            default:
                $result->addError(new Error('Доставка не работает для этой зоны'));

                return $result;
        }

        $stockResult = static::getStocks($basket, $offers, $availableStores);
        $result->setStockResult($stockResult);

        if (!$stockResult->getUnavailable()->isEmpty()) {
            $result->addError(new Error('Присутствуют товары не в наличии'));

            return $result;
        }

        /**
         * Расчет даты доставки
         */
        $result->getDeliveryDate();

        return $result;
    }

    protected function getConfigStructure()
    {
        $result = parent::getConfigStructure();

        $zones = $this->deliveryService->getAvailableZones($this->getId());

        $result['MAIN']['TITLE'] = 'Настройки интервалов';
        $result['MAIN']['DESCRIPTION'] = 'Настройки интервалов';

        $result['MAIN']['ITEMS']['INTERVALS'] = [
            'TYPE' => 'DELIVERY_INTERVALS',
            'NAME' => 'Интервалы доставок',
            'DEFAULT' => [],
            'ZONES' => $zones,
        ];

        $result['PRICES'] = [
            'TITLE' => 'Стоимости доставок по зонам',
            'DESCRIPTION' => 'Стоимости доставок по зонам',
            'ITEMS' => [],
        ];

        $result['FREE_FROM'] = [
            'TITLE' => 'Пороги бесплатной доставки по зонам',
            'DESCRIPTION' => 'Пороги бесплатной доставки по зонам',
            'ITEMS' => [],
        ];

        foreach ($zones as $code => $zone) {
            $result['PRICES']['ITEMS'][$code] = [
                'TYPE' => 'NUMBER',
                'NAME' => 'Зона ' . $zone['NAME'],
                'DEFAULT' => 0,
            ];

            $result['FREE_FROM']['ITEMS'][$code] = [
                'TYPE' => 'NUMBER',
                'NAME' => 'Зона ' . $zone['NAME'],
                'DEFAULT' => 0,
            ];
        }

        return $result;
    }
}
