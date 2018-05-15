<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Service;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Order;
use CUser;

/**
 * Class PaymentService
 *
 * @package FourPaws\SaleBundle\Service
 */
class PaymentService
{
    /**
     * PaymentService constructor.
     */
    public function __construct()
    {
    }

    /**
     * @todo переделать на DTO
     * @todo переделать на сериализацию
     *
     * @param Order $order
     * @param CUser|array $user
     * @param int $taxSystem
     *
     * @return array
     */
    public function getFiscalization(Order $order, $user, int $taxSystem): array
    {
        $amount = 0; //Для фискализации общая сумма берется путем суммирования округленных позиций.
        if ($user instanceof \CUser) {
            $userEmail = $user->GetEmail();
            $userName = $user->GetFullName();
        } else {
            $userEmail = ['email'];
            $userName = ['name'];
        }

        $fiscal = [
            'orderBundle' => [
                'orderCreationDate' => \strtotime($order->getField('DATE_INSERT')),
                'customerDetails' => [
                    'email' => false,
                    'contact' => false,
                ],
                'cartItems' => [
                    'items' => [],
                ],
            ],
            'taxSystem' => $taxSystem,
        ];

        /** @var \Bitrix\Sale\PropertyValue $propertyValue */
        foreach ($order->getPropertyCollection() as $propertyValue) {
            if ($propertyValue->getProperty()['IS_PAYER'] === 'Y') {
                $fiscal['orderBundle']['customerDetails']['contact'] = $propertyValue->getValue();
            } elseif ($propertyValue->getProperty()['IS_EMAIL'] === 'Y') {
                $fiscal['orderBundle']['customerDetails']['email'] = $propertyValue->getValue();
            }
        }

        if (!$fiscal['orderBundle']['customerDetails']['email'] || !$fiscal['orderBundle']['customerDetails']['contact']) {
            if (!$fiscal['orderBundle']['customerDetails']['email']) {
                $fiscal['orderBundle']['customerDetails']['email'] = $userEmail;
            }
            if (!$fiscal['orderBundle']['customerDetails']['contact']) {
                $fiscal['orderBundle']['customerDetails']['contact'] = $userName;
            }
        }

        $measureList = [];
        $dbMeasure = \CCatalogMeasure::getList();
        while ($arMeasure = $dbMeasure->GetNext()) {
            $measureList[$arMeasure['ID']] = $arMeasure['MEASURE_TITLE'];
        }

        $vatList = [];
        $dbRes = \CCatalogVat::GetListEx();
        while ($arRes = $dbRes->Fetch()) {
            $vatList[$arRes['ID']] = $arRes['RATE'];
        }

        $vatGateway = [
            -1 => 0,
            0 => 1,
            10 => 2,
            18 => 3,
        ];

        $itemsCnt = 1;
        $arCheck = null;
        $itemMap = [];

        /** @var \Bitrix\Sale\BasketItem $basketItem */
        foreach ($order->getBasket() as $basketItem) {
            $arProduct = \CCatalogProduct::GetByID($basketItem->getProductId());
            $taxType = $arProduct['VAT_ID'] > 0 ? (int)$vatList[$arProduct['VAT_ID']] : -1;

            $itemAmount = $basketItem->getPrice() * 100;
            if (!($itemAmount % 1)) {
                $itemAmount = \round($itemAmount);
            }

            $amount += $itemAmount * $basketItem->getQuantity(); //Для фискализации общая сумма берется путем суммирования округленных позиций.

            $fiscal['orderBundle']['cartItems']['items'][] = [
                'positionId' => $itemsCnt++,
                'name' => $basketItem->getField('NAME'),
                'quantity' => [
                    'value' => $basketItem->getQuantity(),
                    'measure' => $measureList[$arProduct['MEASURE']],
                ],
                'itemAmount' => $itemAmount * $basketItem->getQuantity(),
                'itemCode' => $basketItem->getProductId(),
                'itemPrice' => $itemAmount,
                'tax' => [
                    'taxType' => $vatGateway[$taxType],
                ],
            ];

            $itemMap[(int)\preg_replace('~^(.*#)~', '', $basketItem->getField('PRODUCT_XML_ID'))] = $basketItem->getProductId();
        }

        if ($order->getDeliveryPrice() > 0) {
            $fiscal['orderBundle']['cartItems']['items'][] = [
                'positionId' => $itemsCnt + 1,
                'name' => Loc::getMessage('RBS_PAYMENT_DELIVERY_TITLE'),
                'quantity' => [
                    'value' => 1,
                    'measure' => Loc::getMessage('RBS_PAYMENT_MEASURE_DEFAULT'),
                ],
                'itemAmount' => $order->getDeliveryPrice() * 100,
                'itemCode' => $order->getId() . '_DELIVERY',
                'itemPrice' => $order->getDeliveryPrice() * 100,
                'tax' => [
                    'taxType' => 0,
                ],
            ];

            $amount += $order->getDeliveryPrice() * 100; //Для фискализации общая сумма берется путем суммирования округленных позиций.
        }

        return \compact('amount', 'fiscal', 'itemMap');
    }
}
