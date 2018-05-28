<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Currency\CurrencyLangTable;

class CurrencyRubleTemplate20180522151111 extends SprintMigrationBase
{
    protected $description = 'Вывод нулей по умолчанию';

    const CURRENCY_CODE = 'RUB';

    protected $languageIds = ['ru', 'en'];

    public function up()
    {
        foreach ($this->languageIds as $lid) {
            $updateResult = CurrencyLangTable::update(
                [
                    'CURRENCY' => self::CURRENCY_CODE,
                    'LID'      => $lid,
                ],
                [
                    'HIDE_ZERO' => 'N'
                ]
            );

            if ($updateResult->isSuccess()) {
                $this->log()->info('Задан формат вывода валюты для ' . $lid);
            } else {
                $this->log()->error('Не удалось задать формат вывода валюты для ' . $lid);

                return false;
            }
        }

        return true;
    }

    public function down()
    {
        foreach ($this->languageIds as $lid) {

            $updateResult = CurrencyLangTable::update(
                [
                    'CURRENCY' => self::CURRENCY_CODE,
                    'LID'      => $lid,
                ],
                [
                    'HIDE_ZERO' => 'N'
                ]
            );

            if ($updateResult->isSuccess()) {
                $this->log()->info('Задан формат вывода валюты для ' . $lid);
            } else {
                $this->log()->error('Не удалось задать формат вывода валюты для ' . $lid);

                return false;
            }
        }

        return true;
    }
}
