<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Highloadblock\DataManager;
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Filter\PriceFilter;

class CatalogFilterAddPriceFilter20180302012614 extends SprintMigrationBase
{
    const FILTER_HL_BLOCK_SERVICE = 'bx.hlblock.filter';

    protected $description = 'Добавление фильтра "Назначение (функциональность)" в HL-блок с фильтрами';
    protected $filters = [
        [
            'UF_NAME' => 'Цена',
            'UF_SORT' => 50,
            'UF_CLASS_NAME' => PriceFilter::class,
            'UF_ACTIVE' => 1,
            'UF_CODE' => 'PRICE',
        ],
    ];

    public function up()
    {
        /** @var DataManager $dataManager */
        $dataManager = Application::getInstance()->getContainer()->get(self::FILTER_HL_BLOCK_SERVICE);

        foreach ($this->filters as $filter) {
            $addResult = $dataManager::add($filter);
            if ($addResult->isSuccess()) {
                $this->log()->info('Добавлен фильтр ' . $filter['UF_NAME']);
            } else {
                $this->log()->warning(
                    'Не удалось добавить фильтр: ' . implode(', ', $addResult->getErrorMessages())
                );
            }
        }
    }

    public function down()
    {
        /**
         * Нет необходимости обратного изменения
         */
    }
}
