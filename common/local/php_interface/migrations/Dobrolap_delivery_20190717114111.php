<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Delivery\Services\Configurable;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Table as ServicesTable;
use FourPaws\DeliveryBundle\Handler\DobrolapDeliveryHandler;

class Dobrolap_delivery_20190717114111 extends SprintMigrationBase
{
    protected $description = "Доставка в приюты (Добролап)";

    protected $defaultFields = [
        'CODE'                => null,
        'PARENT_ID'           => null,
        'NAME'                => '',
        'ACTIVE'              => 'Y',
        'DESCRIPTION'         => '',
        'SORT'                => 100,
        'LOGOTIP'             => null,
        'CONFIG'              => null,
        'CLASS_NAME'          => null,
        'CURRENCY'            => 'RUB',
        'ALLOW_EDIT_SHIPMENT' => 'Y',
    ];

    protected $dobrolapDeliveryCode = 'dobrolap_delivery';

    protected $dobrolapDelivery = [
        'NAME'        => 'Доставка в приют',
        'DESCRIPTION' => 'Ваш заказ будет доставлен в выбранный Вами приют для бездомных животных. После оплаты заказа вы получите сюрприз и памятный магнит.',
        'CLASS_NAME'  => DobrolapDeliveryHandler::class,
        'CONFIG'      => [
            'MAIN' => [
                'CURRENCY' => 'RUB',
                'PRICE'    => 0,
                'PERIOD'   => [
                    'FROM' => 0,
                    'TO'   => 0
                ]
            ]
        ]
    ];

    protected $parentName = 'Актуальные группы доставки';

    public function up()
    {
        $groupId = Manager::getGroupId($this->parentName);
        if (!$groupId) {
            $this->log()->error('Не найдена группа доставок ' . $this->parentName);

            return false;
        }

        $deliveryServices = ServicesTable::getList(
            [
                'filter' => [
                    'CODE' => [
                        $this->dobrolapDeliveryCode
                    ]
                ]
            ]
        );

        while ($deliveryService = $deliveryServices->fetch()) {
            $this->log()->info('Доставка ' . $deliveryService['CODE'] . ' уже существует');
            return false;
        }


        $className = '\\' . $this->dobrolapDelivery['CLASS_NAME'];
        $this->dobrolapDelivery['CLASS_NAME'] = $className;
        $this->dobrolapDelivery = array_merge(
            $this->defaultFields,
            [
                'NAME'        => $className::getClassTitle(),
                'DESCRIPTION' => $className::getClassDescription(),
                'CODE'        => $this->dobrolapDeliveryCode,
                'PARENT_ID'   => $groupId,
            ],
            $this->dobrolapDelivery
        );
        $addResult = ServicesTable::add($this->dobrolapDelivery);
        if ($addResult->isSuccess()) {
            $this->log()->info('Доставка ' . $this->dobrolapDeliveryCode . ' создана');
        } else {
            $this->log()->error('Ошибка при создании доставки ' . $this->dobrolapDeliveryCode);
            return false;
        }
        return true;
    }

    public function down()
    {
        $deliveryServices = ServicesTable::getList(
            [
                'filter' => [
                    'CODE' => [
                        $this->dobrolapDeliveryCode
                    ]
                ],
            ]
        );

        while ($deliveryService = $deliveryServices->fetch()) {
            $deleteResult = ServicesTable::delete($deliveryService['ID']);
            if ($deleteResult->isSuccess()) {
                $this->log()->info('Удалена доставка ' . $deliveryService['CODE']);
            } else {
                $this->log()->error('Ошибка при удалении ' . $deliveryService['CODE']);
                return false;
            }
        }

        return true;
    }
}
