<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\PaySystemActionTable;

class Payment_system_codes20180205112607 extends SprintMigrationBase
{
    protected $description = 'Настройка платежных систем - задание кодов';

    protected $nameToCode = [
        'Наличными при доставке' => 'cash',
        'Картой при доставке'    => 'card',
        'Оплата картой на сайте' => 'card-online',
        'Внутренний счет'        => 'inner',
    ];

    public function up()
    {
        $payments = PaySystemActionTable::getList();

        while ($payment = $payments->fetch()) {
            if (!$code = $this->nameToCode[$payment['NAME']]) {
                continue;
            }

            $updateResult = PaySystemActionTable::update($payment['ID'], ['CODE' => $code]);
            if ($updateResult->isSuccess()) {
                $this->log()->info(
                    sprintf('Задан код для платежной системы %s', $payment['NAME'])
                );
            } else {
                $this->log()->error(
                    sprintf(
                        'Ошибка при редактировании платежной системы %s: ' . implode(
                            ',',
                            $updateResult->getErrorMessages()
                        )
                    ),
                    $payment['NAME']
                );

                return false;
            }
        }

        return true;
    }

    public function down()
    {
    }
}
