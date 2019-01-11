<?php

use Bitrix\Sale\Delivery\Services\Table as DeliveryTable;

IncludeModuleLangFile(__FILE__);

class articul_dostavista_delivery extends CModule
{
    var $MODULE_ID = "articul.dostavista.delivery";

    var $MODULE_NAME;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    static $data = [
        'CODE' => 'dostavista',
        'PARENT_ID' => 12, //TODO Актуальные группы доставки захардкодил
        'NAME' => 'Доставка "Достависта"',
        'ACTIVE' => 'Y',
        'DESCRIPTION' => 'Обработчик доставки "Достависта"',
        'SORT' => '150',
        'LOGOTIP' => '',
        'CONFIG' => '',
//        'CLASS_NAME' => 'FourPaws\DeliveryBundle\Handler\DostavistaDeliveryHandler',
        'CURRENCY' => 'RUB',
        'TRACKING_PARAMS' => '{}',
        'ALLOW_EDIT_SHIPMENT' => 'Y',
        'VAT_ID' => '0'
    ];

    public function __construct()
    {
        if (file_exists(__DIR__ . '/version.php')) {
            $arModuleVersion = [];

            include __DIR__ . '/version.php';

            $this->MODULE_NAME = GetMessage('ARTICUL_DOSTAVISTA_DELIVERY_NAME');
            $this->MODULE_DESCRIPTION = GetMessage('ARTICUL_DOSTAVISTA_DELIVERY_DESCRIPTION');
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            $this->PARTNER_NAME = 'Articul Media';
            $this->PARTNER_URI = 'http://articulmedia.ru/';
        }
    }

    public function DoInstall()
    {
        RegisterModule($this->MODULE_ID);
        CopyDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        $this->DoInstallDelivery();
    }

    public function DoUninstall()
    {
        DeleteDirFiles(__DIR__ . '/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        UnRegisterModule($this->MODULE_ID);
        $this->DoUninstallDelivery();
    }

    private function DoInstallDelivery()
    {
        DeliveryTable::add(static::$data);
    }

    private function DoUninstallDelivery()
    {
        $deliveryId = Bitrix\Sale\Delivery\Services\Manager::getIdByCode(static::$data['CODE']);
        if ($deliveryId) {
            DeliveryTable::delete($deliveryId);
        }
    }
}
