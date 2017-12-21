<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Currency\CurrencyLangTable;

class Currency_rub_template20171219151247 extends SprintMigrationBase
{
    protected $description = 'Задание дефолтного шаблона валюты в соответствии с дизайном';

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
                    'FORMAT_STRING' => '# ₽',
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
            $format = '';
            switch ($lid) {
                case 'ru':
                    $format = '# руб.';
                    break;
                case 'en':
                    $format = '# rub.';
                    break;
            }
            $updateResult = CurrencyLangTable::update(
                [
                    'CURRENCY' => self::CURRENCY_CODE,
                    'LID'      => $lid,
                ],
                [
                    'FORMAT_STRING' => $format,
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
