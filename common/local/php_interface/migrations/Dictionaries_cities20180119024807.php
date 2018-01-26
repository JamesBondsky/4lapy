<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Location\LocationTable;
use CApplicationException;
use CUtil;
use FourPaws\App\Application;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\CitiesSectionCode;
use RuntimeException;
use CIBlockElement;

class Dictionaries_cities20180119024807 extends SprintMigrationBase
{
    protected $description = "Создание и заполнение ИБ \"Города\"";

    const SITE_ID = 's1';

    protected $properties = [
        'LOCATION' => [
            'NAME'          => 'Местоположение',
            'CODE'          => 'LOCATION',
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE'     => 'sale_location',
            'IS_REQUIRED'   => 'Y',
        ],
    ];

    protected $sections = [
        CitiesSectionCode::POPULAR       => [
            'NAME' => 'Популярные города',
            'CODE' => CitiesSectionCode::POPULAR,
        ],
        CitiesSectionCode::MOSCOW_REGION => [
            'NAME' => 'Населенные пункты Московской области',
            'CODE' => CitiesSectionCode::MOSCOW_REGION,
        ],
    ];

    public function up()
    {
        if (!$iblockId = $this->addIblock()) {
            $this->logError('Ошибка при создании инфоблока ' . IblockCode::CITIES);

            return false;
        } else {
            $this->log()->info('Создан ИБ ' . IblockCode::CITIES);
        }

        /** @var \Sprint\Migration\Helpers\IblockHelper $iblockHelper */
        $iblockHelper = $this->getHelper()->Iblock();

        foreach ($this->properties as $code => $fields) {
            if (!$iblockHelper->addPropertyIfNotExists($iblockId, $fields)) {
                $this->logError('Ошибка при добавлении свойства ' . $code . ' в ИБ с ID=' . $iblockId);

                return false;
            } else {
                $this->log()->info('Создано свойство ' . $code . ' в ИБ с ID=' . $iblockId);
            }
        }

        foreach ($this->sections as $code => $fields) {
            if (!$sectionId = $iblockHelper->addSectionIfNotExists($iblockId, $fields)) {
                $this->logError('Ошибка при добавлении раздела ' . $fields['NAME'] . ' в ИБ с ID=' . $iblockId);

                return false;
            } else {
                $this->log()->info('Создан раздел ' . $fields['CODE'] . ' в ИБ с ID=' . $iblockId);
            }

            $this->sections[$code]['ID'] = $sectionId;
        }

        $elements = CIBlockElement::GetList([], ['IBLOCK_ID' => $iblockId]);
        while ($element = $elements->Fetch()) {
            if (CIBlockElement::Delete($element['ID'])) {
                $this->log()->info('Удален элемент ' . $element['CODE']);
            } else {
                $this->log()->warning('Ошибка при удалении элемента ' . $element['CODE']);
            }
        }

        $citiesList = $this->loadCitiesList();
        foreach ($citiesList as $sectionCode => $cities) {
            $names = array_column($cities, 'NAME');
            $nameToLocation = [];
            $dbLocations = LocationTable::getList(['filter' => ['=NAME.NAME' => $names], 'select' => ['CODE', 'NAME.NAME']]);
            while ($location = $dbLocations->fetch()) {
                $nameToLocation[$location['SALE_LOCATION_LOCATION_NAME_NAME']] = $location['CODE'];
            }

            foreach ($cities as $city) {
                if (!isset($nameToLocation[$city['NAME']])) {
                    switch (mb_strtoupper($city['NAME'])) {
                        case 'БЫКОВО':
                            $nameToLocation[$city['NAME']] = '0000059219';
                            break;
                        case 'КРАСКОВО':
                            $nameToLocation[$city['NAME']] = '0000046135';
                            break;
                        case 'ТОМИЛИНО':
                            $nameToLocation[$city['NAME']] = '0000046724';
                            break;
                        default:
                            $this->log()->warning('Не найдено местоположение для города ' . $city['NAME']);
                            continue 2;
                    }
                }

                $code = CUtil::translit(mb_strtolower($city['NAME']), 'ru') . '_' . $sectionCode;

                $fields = [
                    'NAME'              => $city['NAME'],
                    'CODE'              => $code,
                    'XML_ID'            => $code,
                    'IBLOCK_SECTION_ID' => $this->sections[$sectionCode]['ID'],
                    'SORT'              => $city['SORT'],
                ];

                $props = ['LOCATION' => $nameToLocation[$city['NAME']]];

                if (!$iblockHelper->addElementIfNotExists($iblockId, $fields, $props)) {
                    $this->logError('Ошибка при добавлении элемента ' . $city['NAME']);
                } else {
                    $this->log()->info('Создан элемент ' . $city['NAME']);
                }
            }
        }

        return true;
    }

    public function down()
    {
        /** @var \Sprint\Migration\Helpers\IblockHelper $iblockHelper */
        $iblockHelper = $this->getHelper()->Iblock();

        if (!$iblockHelper->deleteIblockIfExists(IblockCode::CITIES, IblockType::REFERENCE_BOOKS)) {
            $this->logError('Ошибка при удалении инфоблока ' . IblockCode::CITIES);

            return false;
        } else {
            $this->log()->info('Удален иб ' . IblockCode::CITIES);
        }

        return true;
    }

    private function addIblock()
    {
        return $this->getHelper()->Iblock()->addIblockIfNotExists(
            [
                'NAME'           => 'Города',
                'CODE'           => IblockCode::CITIES,
                'IBLOCK_TYPE_ID' => IblockType::REFERENCE_BOOKS,
                'SITE_ID'        => [static::SITE_ID],
            ]
        );
    }

    private function loadCitiesList()
    {
        $filePath = Application::getAbsolutePath('/local/php_interface/migration_sources/cities.csv');

        $fp = fopen($filePath, 'rb');
        if (false === $fp) {
            throw new RuntimeException(
                sprintf(
                    'Can not open file %s',
                    $filePath
                )
            );
        }

        $cityList = [];
        while ($row = fgetcsv($fp)) {
            $cityList[trim($row[1])][] = [
                'NAME' => trim($row[0]),
                'SORT' => trim($row[2]),
            ];
        }

        return $cityList;
    }

    private function logError($message)
    {
        global $APPLICATION;
        $exception = $APPLICATION->GetException();
        $errorMessage = '<null>';
        if ($exception instanceof CApplicationException) {
            $errorMessage = $exception->GetString();
        }

        $this->log()->error($message . ': ' . $errorMessage);
    }

}
