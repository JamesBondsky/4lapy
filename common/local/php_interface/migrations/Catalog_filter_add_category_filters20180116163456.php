<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Highloadblock\DataManager;
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Filter\CategoryFilter;
use FourPaws\Catalog\Model\Filter\SectionFilter;

class Catalog_filter_add_category_filters20180116163456 extends SprintMigrationBase
{
    protected $description = 'Добавление новых классов фильтров в HL-блок с фильтрами';

    protected $filters = [
        [
            'UF_NAME'       => 'Раздел',
            'UF_SORT'       => 10,
            'UF_CLASS_NAME' => SectionFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'sectionIdList',
        ],
        [
            'UF_NAME'       => 'Категория',
            'UF_SORT'       => 50,
            'UF_CLASS_NAME' => CategoryFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'sectionIdList',
        ]
    ];

    const FILTER_HL_BLOCK_SERVICE = 'bx.hlblock.filter';

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
