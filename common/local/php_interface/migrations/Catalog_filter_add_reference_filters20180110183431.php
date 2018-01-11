<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Highloadblock\DataManager;
use FourPaws\App\Application;
use FourPaws\Catalog\Model\Filter\ClothingSizeFilter;
use FourPaws\Catalog\Model\Filter\ColourFilter;
use FourPaws\Catalog\Model\Filter\ConsistenceFilter;
use FourPaws\Catalog\Model\Filter\CountryFilter;
use FourPaws\Catalog\Model\Filter\FeedSpecFilter;
use FourPaws\Catalog\Model\Filter\FlavourFilter;
use FourPaws\Catalog\Model\Filter\ManufactureMaterialFilter;
use FourPaws\Catalog\Model\Filter\PackageTypeFilter;
use FourPaws\Catalog\Model\Filter\PetTypeFilter;
use FourPaws\Catalog\Model\Filter\PharmaGroupFilter;
use FourPaws\Catalog\Model\Filter\PurposeFilter;
use FourPaws\Catalog\Model\Filter\SeasonFilter;
use FourPaws\Catalog\Model\Filter\TradeNameFilter;
use FourPaws\Catalog\Model\Filter\VolumeReferenceFilter;

class Catalog_filter_add_reference_filters20180110183431 extends SprintMigrationBase
{
    protected $description = 'Добавление новых классов фильтров (по свойствам-справочникам) в HL-блок с фильтрами';

    protected $filters = [
        [
            'UF_NAME'       => 'Тип корма',
            'UF_SORT'       => 800,
            'UF_CLASS_NAME' => ConsistenceFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'CONSISTENCE',
        ],
        [
            'UF_NAME'       => 'Страна-производитель',
            'UF_SORT'       => 900,
            'UF_CLASS_NAME' => CountryFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'COUNTRY',
        ],
        [
            'UF_NAME'       => 'Специальные показания',
            'UF_SORT'       => 1000,
            'UF_CLASS_NAME' => FeedSpecFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'FEED_SPECIFICATION',
        ],
        [
            'UF_NAME'       => 'Вкус корма',
            'UF_SORT'       => 1100,
            'UF_CLASS_NAME' => FlavourFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'FLAVOUR',
        ],
        [
            'UF_NAME'       => 'Материал',
            'UF_SORT'       => 1200,
            'UF_CLASS_NAME' => ManufactureMaterialFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'MANUFACTURE_MATERIAL',
        ],
        [
            'UF_NAME'       => 'Вид животного',
            'UF_SORT'       => 1300,
            'UF_CLASS_NAME' => PetTypeFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'PET_TYPE',
        ],
        [
            'UF_NAME'       => 'Назначение',
            'UF_SORT'       => 1400,
            'UF_CLASS_NAME' => PharmaGroupFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'PHARMA_GROUP',
        ],
        [
            'UF_NAME'       => 'Тип оборудования',
            'UF_SORT'       => 1500,
            'UF_CLASS_NAME' => PurposeFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'PURPOSE',
        ],
        [
            'UF_NAME'       => 'Производитель',
            'UF_SORT'       => 1600,
            'UF_CLASS_NAME' => TradeNameFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'TRADE_NAME',
        ],
        [
            'UF_NAME'       => 'Цвет',
            'UF_SORT'       => 1700,
            'UF_CLASS_NAME' => ColourFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'COLOUR',
        ],
        [
            'UF_NAME'       => 'Размер',
            'UF_SORT'       => 1800,
            'UF_CLASS_NAME' => ClothingSizeFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'CLOTHING_SIZE',
        ],
        [
            'UF_NAME'       => 'Объем',
            'UF_SORT'       => 1900,
            'UF_CLASS_NAME' => VolumeReferenceFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'VOLUME_REFERENCE',
        ],
        [
            'UF_NAME'       => 'Сезон',
            'UF_SORT'       => 2000,
            'UF_CLASS_NAME' => SeasonFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'SEASON_CLOTHES',
        ],
        [
            'UF_NAME'       => 'Тип упаковки',
            'UF_SORT'       => 2100,
            'UF_CLASS_NAME' => PackageTypeFilter::class,
            'UF_ACTIVE'     => 1,
            'UF_CODE'       => 'KIND_OF_PACKING',
        ],
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
