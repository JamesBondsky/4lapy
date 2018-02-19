<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\SaleBundle\Service\OrderService;
use CSalePaySystemAction;

class Payment_system_action_file20180214172905 extends SprintMigrationBase
{
    protected $description = 'Изменение обработчика платежной системы "онлайн"';

    const ACTION_FILE_OLD = '/bitrix/php_interface/include/sale_payment/payment';

    const ACTION_FILE_NEW = '/local/php_interface/include/sale_payment/payment';

    public function up()
    {
        if (!$paySystem = CSalePaySystemAction::GetList([], ['CODE' => OrderService::PAYMENT_ONLINE])->Fetch()) {
            $this->log()->error('Не найден платежная система ' . OrderService::PAYMENT_ONLINE);

            return false;
        }

        $ps = new CSalePaySystemAction();
        $ps->Update($paySystem['ID'], ['ACTION_FILE' => self::ACTION_FILE_NEW]);

        return true;
    }

    public function down()
    {
        if (!$paySystem = CSalePaySystemAction::GetList([], ['CODE' => OrderService::PAYMENT_ONLINE])->Fetch()) {
            $this->log()->error('Не найден платежная система ' . OrderService::PAYMENT_ONLINE);

            return false;
        }

        $ps = new CSalePaySystemAction();
        $ps->Update($paySystem['ID'], ['ACTION_FILE' => self::ACTION_FILE_OLD]);

        return true;
    }
}
