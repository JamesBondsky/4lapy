<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\PaySystemActionTable;

class PaymentSystemCodes20180503173657 extends SprintMigrationBase
{
    protected $description = 'Настройка платежных систем - задание кодов';

    protected $nameToCode = [
        'cash' => [
            'NAME' => 'Наличными или картой при получении',
            'PSA_NAME' => 'Наличными или картой при получении',
            'CODE' => 'cash-or-card',
        ],
        'card' => [
            'NAME' => 'Наличными при получении',
            'PSA_NAME' => 'Наличными при получении',
            'CODE' => 'cash'
        ]
    ];

    public function up()
    {
        $payments = PaySystemActionTable::getList();

        while ($payment = $payments->fetch()) {
            if (!$newData = $this->nameToCode[$payment['CODE']]) {
                continue;
            }

            $updateResult = PaySystemActionTable::update($payment['ID'], $newData);
            if ($updateResult->isSuccess()) {
                $this->log()->info(
                    sprintf('Задан код для платежной системы %s', $newData['NAME'])
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
