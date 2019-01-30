<?php

use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Handler\DostavistaDeliveryHandler;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Table as ServicesTable;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;

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

    protected $defaultFields = [
        'CODE' => null,
        'PARENT_ID' => null,
        'NAME' => '',
        'ACTIVE' => 'Y',
        'DESCRIPTION' => '',
        'SORT' => 100,
        'LOGOTIP' => null,
        'CONFIG' => null,
        'CLASS_NAME' => null,
        'CURRENCY' => 'RUB',
        'ALLOW_EDIT_SHIPMENT' => 'Y',
    ];

    protected $deliveries = [
        'dostavista' => [
            'CLASS_NAME' => DostavistaDeliveryHandler::class,
            'CONFIG' => [
                'MAIN' => [
                    'CURRENCY' => 'RUB',
                ],
            ],
        ]
    ];

    protected $restrictions = [
        'dostavista' => [
            [
                'CLASS_NAME' => '\Bitrix\Sale\Delivery\Restrictions\ByLocation',
                'ITEMS' => [
                    [
                        'LOCATION_CODE' => '0000073738',
                        'LOCATION_TYPE' => DeliveryService::LOCATION_RESTRICTION_TYPE_LOCATION,
                    ]
                ],
            ],
        ]
    ];

    protected $parentName = 'Актуальные группы доставки';

    protected $fieldsStore = [
        'UF_DELIVERY_TIME' => [
            'FIELD_NAME' => 'UF_AVAILABLE_EXPRESS',
            'USER_TYPE_ID' => 'boolean',
            'XML_ID' => 'UF_AVAILABLE_EXPRESS',
            'SORT' => 1400,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'Y',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'EDIT_FORM_LABEL' => [
                'ru' => 'Доступен для экспресс доставки',
                'en' => 'Available for express delivery',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Доступен для экспресс доставки',
                'en' => 'Available for express delivery',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Доступен для экспресс доставки',
                'en' => 'Available for express delivery',
            ],
            'SETTINGS' => [
                'DEFAULT_VALUE' => 'N',
            ],
        ],
    ];

    protected $arExpressStores = [
        'R238', 'R239', 'R243', 'R244', 'R245', 'R253', 'R255', 'R247', 'R003', 'R125', 'R189', 'R045', 'R200',
        'R164', 'R183', 'R138', 'R132', 'R251', 'R223', 'R233', 'R234', 'R236', 'R240', 'R170', 'R194', 'R215',
        'R161', 'R202', 'R119', 'R204', 'R209', 'R041', 'R190', 'R081', 'R207', 'R129', 'R047', 'R052', 'R210',
        'R199', 'R043', 'R188', 'R072', 'R112', 'R130', 'R146', 'R093', 'R139', 'R206', 'R218', 'R220', 'R222',
        'R024', 'R185', 'R126', 'R140', 'R101', 'R053', 'R165', 'R113', 'R124', 'R212', 'R176', 'R214'
    ];

    const ENTITY_ID = 'CAT_STORE';

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
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function DoInstallDelivery()
    {
        /** @var StoreService $storeService */
        $storeService = Application::getInstance()->getContainer()->get('store.service');
        foreach ($this->fieldsStore as $field) {
            if (!$this->addStoreField(static::ENTITY_ID, $field)) {
                return false;
            }
        }

        $storeCollection = $storeService->getStores(StoreService::TYPE_ALL, ['XML_ID' => $this->arExpressStores]);
        /** @var Store $store */
        foreach ($storeCollection as $store) {
            $store->setIsExpressStore(true);
            $storeService->saveStore($store);
        }

        $groupId = Manager::getGroupId($this->parentName);
        if (!$groupId) {
            //'Не найдена группа доставок ' . $this->parentName
            return false;
        }

        $deliveryServices = ServicesTable::getList(
            [
                'filter' => [
                    'CODE' => array_keys($this->deliveries),
                ],
            ]
        );

        while ($deliveryService = $deliveryServices->fetch()) {
            //'Доставка ' . $deliveryService['CODE'] . ' уже существует'
            unset($this->deliveries[$deliveryService['CODE']]);
        }

        foreach ($this->deliveries as $code => $fields) {
            $className = '\\' . $fields['CLASS_NAME'];
            $fields['CLASS_NAME'] = $className;
            $fields = array_merge(
                $this->defaultFields,
                $fields,
                [
                    'NAME' => $className::getClassTitle(),
                    'DESCRIPTION' => $className::getClassDescription(),
                    'CODE' => $code,
                    'PARENT_ID' => $groupId,
                ]
            );
            $addResult = ServicesTable::add($fields);
            if ($addResult->isSuccess()) {
                //'Доставка ' . $code . ' создана'
            } else {
                //'Ошибка при создании доставки ' . $code

                return false;
            }
        }

        return true;
    }


    /**
     * @param $entityId
     * @param $field
     * @return bool
     */
    protected function addStoreField($entityId, $fields): bool
    {

        $aItem = $this->getUserTypeEntity($entityId, $fields["FIELD_NAME"]);
        if ($aItem) {
            return $aItem['ID'];
        }

        $default = [
            "ENTITY_ID" => '',
            "FIELD_NAME" => '',
            "USER_TYPE_ID" => '',
            "XML_ID" => '',
            "SORT" => 500,
            "MULTIPLE" => 'N',
            "MANDATORY" => 'N',
            "SHOW_FILTER" => 'I',
            "SHOW_IN_LIST" => '',
            "EDIT_IN_LIST" => '',
            "IS_SEARCHABLE" => '',
            "SETTINGS" => [],
            "EDIT_FORM_LABEL" => ['ru' => '', 'en' => ''],
            "LIST_COLUMN_LABEL" => ['ru' => '', 'en' => ''],
            "LIST_FILTER_LABEL" => ['ru' => '', 'en' => ''],
            "ERROR_MESSAGE" => '',
            "HELP_MESSAGE" => '',
        ];

        $fields = array_replace_recursive($default, $fields);
        $fields['FIELD_NAME'] = $fields["FIELD_NAME"];
        $fields['ENTITY_ID'] = $entityId;

        $obUserField = new \CUserTypeEntity;
        $userFieldId = $obUserField->Add($fields);

        if ($userFieldId) {
            return $userFieldId;
        }

        return true;
    }

    public function getUserTypeEntity($entityId, $fieldName)
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $dbRes = \CUserTypeEntity::GetList([], ['ENTITY_ID' => $entityId, 'FIELD_NAME' => $fieldName]);
        $aItem = $dbRes->Fetch();
        return (!empty($aItem)) ? \CUserTypeEntity::GetByID($aItem['ID']) : false;
    }
}