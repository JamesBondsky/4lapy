<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Highloadblock\DataManager;
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Filter\PharmaGroupFilter;
use FourPaws\Catalog\Model\Filter\PurposeFilter;

class CatalogFilterAddPurposeFilter20180227125133 extends SprintMigrationBase
{
    const FILTER_HL_BLOCK_SERVICE = 'bx.hlblock.filter';

    protected $description = 'Фильтра "Назначение (функциональность)" в HL-блок с фильтрами';
    protected $filters = [
        [
            'UF_NAME' => 'Фармакологическая группа',
            'UF_SORT' => 1400,
            'UF_CLASS_NAME' => PharmaGroupFilter::class,
            'UF_ACTIVE' => 1,
            'UF_CODE' => 'PHARMA_GROUP',
        ],
        [
            'UF_NAME' => 'Назначение',
            'UF_SORT' => 1500,
            'UF_CLASS_NAME' => PurposeFilter::class,
            'UF_ACTIVE' => 1,
            'UF_CODE' => 'PURPOSE',
        ],
    ];

    public function up()
    {
        /** @var DataManager $dataManager */
        $dataManager = Application::getInstance()->getContainer()->get(self::FILTER_HL_BLOCK_SERVICE);

        foreach ($this->filters as $filter) {
            $id = $dataManager::getList(['filter' => ['UF_CODE' => $filter['UF_CODE']], 'select' => ['ID']])->fetch();

            $addResult = $dataManager::update($id['ID'], $filter);
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
