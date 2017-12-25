<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Location\TypeTable;
use FourPaws\Location\LocationService;

class Delivery_ipol_dpd_module_configuration20171218162911 extends SprintMigrationBase
{
    protected $description = 'Конфигурация модуля ipol.dpd';

    const MODULE_ID = 'ipol.dpd';

    protected $values = [
        'IS_TEST'              => 1,
        'KLIENT_CURRENCY'      => 'RUB',
        'KLIENT_KEY'           => 'D34538D623B7BF2E03EE6BB59A5DF8DCF305B575',
        'KLIENT_NUMBER'        => '1001041602',
        'RECEIVER_EMAIL_1'     => 'EMAIL',
        'RECEIVER_FIO_1'       => 'NAME',
        'RECEIVER_LOCATION_1'  => 'CITY_CODE',
        'RECEIVER_NAME_1'      => 'NAME',
        'RECEIVER_PHONE_1'     => 'PHONE',
        'SENDER_FIO'           => '4lapy',
        'SENDER_NAME'          => '4lapy',
        'SENDER_PHONE'         => '+7 800 770-00-22',
    ];

    public function up()
    {
        $types = TypeTable::getList(
            [
                'filter' => [
                    'CODE' => [
                        'CITY',
                        'COUNTRY',
                        'REGION',
                        'VILLAGE',
                    ],
                ],
            ]
        );
        while ($type = $types->fetch()) {
            $this->values['LOCATION_' . $type['CODE']] = $type['ID'];
        }

        $location = LocationTable::getList(
            [
                'filter' => ['CODE' => LocationService::LOCATION_CODE_MOSCOW],
                'select' => ['NAME.NAME', 'ID', 'CODE'],
            ]
        )->fetch();

        if (!$location) {
            $this->log()->error('Не найдено дефолтное местоположение');

            return false;
        }

        $this->values['SENDER_LOCATION'] = $location['ID'];

        foreach ($this->values as $code => $value) {
            Option::set(self::MODULE_ID, $code, $value);
        }

        return true;
    }

    public function down()
    {
    }
}
