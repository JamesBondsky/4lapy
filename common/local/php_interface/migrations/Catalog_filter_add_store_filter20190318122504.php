<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Entity\DataManager;
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Filter\StoreAvailabilityFilter;

class Catalog_filter_add_store_filter20190318122504 extends SprintMigrationBase
{
    protected $description = 'Добавление фильтра по доступности в магазине в каталог';

    protected $filters = [
        [
            'UF_NAME'       => 'Доступность в магазине',
            'UF_SORT'       => 2100,
            'UF_CLASS_NAME' => StoreAvailabilityFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'storeAvailability',
        ],
    ];

    protected const FILTER_HL_BLOCK_SERVICE = 'bx.hlblock.filter';

    public function up()
    {
        /** @var DataManager $dataManager */
        $dataManager = Application::getInstance()->getContainer()->get(self::FILTER_HL_BLOCK_SERVICE);

        foreach ($this->filters as $filter) {
            $addResult = $dataManager->add($filter);
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
        /** @var DataManager $dataManager */
        $dataManager = Application::getInstance()->getContainer()->get(self::FILTER_HL_BLOCK_SERVICE);

        $filters = $dataManager->getList(
            [
                'filter' => ['UF_CODE' => array_column($this->filters, 'UF_CODE')],
            ]
        );
        while ($filter = $filters->fetch()) {
            $deleteResult = $dataManager->delete($filter['ID']);
            if ($deleteResult->isSuccess()) {
                $this->log()->info('Удален фильтр ' . $filter['UF_NAME']);
            } else {
                $this->log()->warning(
                    'Не удалось удалить фильтр: ' . implode(', ', $deleteResult->getErrorMessages())
                );
            }
        }
    }
}
