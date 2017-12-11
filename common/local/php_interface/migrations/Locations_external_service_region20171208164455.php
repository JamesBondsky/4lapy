<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Location\ExternalTable;
use Bitrix\Sale\Location\ExternalServiceTable;
use Bitrix\Sale\Location\LocationTable;
use FourPaws\App\Application;
use FourPaws\Location\LocationService;
use FourPaws\UserBundle\Exception\RuntimeException;

class Locations_external_service_region20171208164455 extends SprintMigrationBase
{
    protected $description = 'Создание внешнего сервиса REGION для местоположений и привязка регионов к типам цен';

    const EXTERNAL_CODE = 'REGION';

    protected $regions = [
        [
            'NAME'   => 'Московская область',
            'CODE'   => '0000028025',
            'XML_ID' => 'IR50',
        ],
        [
            'NAME'   => 'Белгородская область',
            'CODE'   => '0000028026',
            'XML_ID' => 'IR31',
        ],
        [
            'NAME'   => 'Ивановская область',
            'CODE'   => '0000028027',
            'XML_ID' => 'IR37',
        ],
        [
            'NAME'   => 'Калужская область',
            'CODE'   => '0000028028',
            'XML_ID' => 'IR40',
        ],
        [
            'NAME'   => 'Костромская область',
            'CODE'   => '0000028029',
            'XML_ID' => 'IR44',
        ],
        [
            'NAME'   => 'Курская область',
            'CODE'   => '0000028030',
            'XML_ID' => 'IR46',
        ],
        [
            'NAME'   => 'Липецкая область',
            'CODE'   => '0000028031',
            'XML_ID' => 'IR48',
        ],
        [
            'NAME'   => 'Орловская область',
            'CODE'   => '0000028032',
            'XML_ID' => 'IR57',
        ],
        [
            'NAME'   => 'Рязанская область',
            'CODE'   => '0000028033',
            'XML_ID' => 'IR62',
        ],
        [
            'NAME'   => 'Смоленская область',
            'CODE'   => '0000028034',
            'XML_ID' => 'IR67',
        ],
        [
            'NAME'   => 'Тамбовская область',
            'CODE'   => '0000028035',
            'XML_ID' => 'IR68',
        ],
        [
            'NAME'   => 'Тверская область',
            'CODE'   => '0000028036',
            'XML_ID' => 'IR69',
        ],
        [
            'NAME'   => 'Тульская область',
            'CODE'   => '0000028037',
            'XML_ID' => 'IR71',
        ],
        [
            'NAME'   => 'Ярославская область',
            'CODE'   => '0000028038',
            'XML_ID' => 'IR76',
        ],
        [
            'NAME'   => 'Брянская область',
            'CODE'   => '0000028039',
            'XML_ID' => 'IR32',
        ],
        [
            'NAME'   => 'Воронежская область',
            'CODE'   => '0000028040',
            'XML_ID' => 'IR36',
        ],
        [
            'NAME'   => 'Владимирская область',
            'CODE'   => '0000028041',
            'XML_ID' => 'IR33',
        ],
        [
            'NAME'   => 'Ленинградская область',
            'CODE'   => '0000028043',
            'XML_ID' => 'IR47',
        ],
        [
            'NAME'   => 'Республика Коми',
            'CODE'   => '0000028044',
            'XML_ID' => 'IR11',
        ],
        [
            'NAME'   => 'Республика Карелия',
            'CODE'   => '0000028045',
            'XML_ID' => 'IR10',
        ],
        [
            'NAME'   => 'Вологодская область',
            'CODE'   => '0000028046',
            'XML_ID' => 'IR35',
        ],
        [
            'NAME'   => 'Архангельская область',
            'CODE'   => '0000028047',
            'XML_ID' => 'IR29',
        ],
        [
            'NAME'   => 'Мурманская область',
            'CODE'   => '0000028048',
            'XML_ID' => 'IR51',
        ],
        [
            'NAME'   => 'Калининградская область',
            'CODE'   => '0000028049',
            'XML_ID' => 'IR39',
        ],
        [
            'NAME'   => 'Псковская область',
            'CODE'   => '0000028050',
            'XML_ID' => 'IR60',
        ],
        [
            'NAME'   => 'Новгородская область',
            'CODE'   => '0000028051',
            'XML_ID' => 'IR53',
        ],
        [
            'NAME'   => 'Ненецкий автономный округ',
            'CODE'   => '0000028052',
            'XML_ID' => 'IR83',
        ],
        [
            'NAME'   => 'Краснодарский край',
            'CODE'   => '0000028054',
            'XML_ID' => 'IR23',
        ],
        [
            'NAME'   => 'Волгоградская область',
            'CODE'   => '0000028055',
            'XML_ID' => 'IR34',
        ],
        [
            'NAME'   => 'Ростовская область',
            'CODE'   => '0000028056',
            'XML_ID' => 'IR61',
        ],
        [
            'NAME'   => 'Астраханская область',
            'CODE'   => '0000028057',
            'XML_ID' => 'IR30',
        ],
        [
            'NAME'   => 'Республика Адыгея',
            'CODE'   => '0000028058',
            'XML_ID' => 'IR01',
        ],
        [
            'NAME'   => 'Республика Калмыкия',
            'CODE'   => '0000028059',
            'XML_ID' => 'IR08',
        ],
        [
            'NAME'   => 'Республика Дагестан',
            'CODE'   => '0000028061',
            'XML_ID' => 'IR05',
        ],
        [
            'NAME'   => 'Кабардино-Балкарская Республика',
            'CODE'   => '0000028062',
            'XML_ID' => 'IR07',
        ],
        [
            'NAME'   => 'Республика Северная Осетия-Алания',
            'CODE'   => '0000028063',
            'XML_ID' => 'IR15',
        ],
        [
            'NAME'   => 'Ставропольский край',
            'CODE'   => '0000028064',
            'XML_ID' => 'IR26',
        ],
        [
            'NAME'   => 'Республика Ингушетия',
            'CODE'   => '0000028065',
            'XML_ID' => 'IR06',
        ],
        [
            'NAME'   => 'Карачаево-Черкесская Республика',
            'CODE'   => '0000028066',
            'XML_ID' => 'IR09',
        ],
        [
            'NAME'   => 'Чеченская Республика',
            'CODE'   => '0000028067',
            'XML_ID' => 'IR20',
        ],
        [
            'NAME'   => 'Республика Мордовия',
            'CODE'   => '0000028069',
            'XML_ID' => 'IR13',
        ],
        [
            'NAME'   => 'Республика Татарстан',
            'CODE'   => '0000028070',
            'XML_ID' => 'IR16',
        ],
        [
            'NAME'   => 'Республика Марий Эл',
            'CODE'   => '0000028071',
            'XML_ID' => 'IR12',
        ],
        [
            'NAME'   => 'Кировская область',
            'CODE'   => '0000028072',
            'XML_ID' => 'IR43',
        ],
        [
            'NAME'   => 'Нижегородская область',
            'CODE'   => '0000028073',
            'XML_ID' => 'IR52',
        ],
        [
            'NAME'   => 'Удмуртская Республика',
            'CODE'   => '0000028074',
            'XML_ID' => 'IR18',
        ],
        [
            'NAME'   => 'Чувашская Республика',
            'CODE'   => '0000028075',
            'XML_ID' => 'IR21',
        ],
        [
            'NAME'   => 'Самарская область',
            'CODE'   => '0000028076',
            'XML_ID' => 'IR63',
        ],
        [
            'NAME'   => 'Пермский край',
            'CODE'   => '0000028077',
            'XML_ID' => 'IR59',
        ],
        [
            'NAME'   => 'Пензенская область',
            'CODE'   => '0000028078',
            'XML_ID' => 'IR58',
        ],
        [
            'NAME'   => 'Оренбургская область',
            'CODE'   => '0000028079',
            'XML_ID' => 'IR56',
        ],
        [
            'NAME'   => 'Республика Башкортостан',
            'CODE'   => '0000028080',
            'XML_ID' => 'IR02',
        ],
        [
            'NAME'   => 'Ульяновская область',
            'CODE'   => '0000028081',
            'XML_ID' => 'IR73',
        ],
        [
            'NAME'   => 'Саратовская область',
            'CODE'   => '0000028082',
            'XML_ID' => 'IR64',
        ],
        [
            'NAME'   => 'Тюменская область',
            'CODE'   => '0000028084',
            'XML_ID' => 'IR72',
        ],
        [
            'NAME'   => 'Свердловская область',
            'CODE'   => '0000028085',
            'XML_ID' => 'IR66',
        ],
        [
            'NAME'   => 'Курганская область',
            'CODE'   => '0000028086',
            'XML_ID' => 'IR45',
        ],
        [
            'NAME'   => 'Ямало-Ненецкий автономный округ',
            'CODE'   => '0000028087',
            'XML_ID' => 'IR89',
        ],
        [
            'NAME'   => 'Ханты-Мансийский автономный округ',
            'CODE'   => '0000028088',
            'XML_ID' => 'IR86',
        ],
        [
            'NAME'   => 'Челябинская область',
            'CODE'   => '0000028089',
            'XML_ID' => 'IR74',
        ],
        [
            'NAME'   => 'Иркутская область',
            'CODE'   => '0000028091',
            'XML_ID' => 'IR38',
        ],
        [
            'NAME'   => 'Красноярский край',
            'CODE'   => '0000028092',
            'XML_ID' => 'IR24',
        ],
        [
            'NAME'   => 'Забайкальский край',
            'CODE'   => '0000028093',
            'XML_ID' => 'IR75',
        ],
        [
            'NAME'   => 'Кемеровская область',
            'CODE'   => '0000028094',
            'XML_ID' => 'IR42',
        ],
        [
            'NAME'   => 'Новосибирская область',
            'CODE'   => '0000028095',
            'XML_ID' => 'IR54',
        ],
        [
            'NAME'   => 'Омская область',
            'CODE'   => '0000028096',
            'XML_ID' => 'IR55',
        ],
        [
            'NAME'   => 'Томская область',
            'CODE'   => '0000028097',
            'XML_ID' => 'IR70',
        ],
        [
            'NAME'   => 'Алтайский край',
            'CODE'   => '0000028098',
            'XML_ID' => 'IR22',
        ],
        [
            'NAME'   => 'Республика Бурятия',
            'CODE'   => '0000028099',
            'XML_ID' => 'IR03',
        ],
        [
            'NAME'   => 'Республика Хакасия',
            'CODE'   => '0000028100',
            'XML_ID' => 'IR19',
        ],
        [
            'NAME'   => 'Республика Тыва',
            'CODE'   => '0000028101',
            'XML_ID' => 'IR17',
        ],
        [
            'NAME'   => 'Республика Алтай',
            'CODE'   => '0000028102',
            'XML_ID' => 'IR04',
        ],
        [
            'NAME'   => 'Хабаровский край',
            'CODE'   => '0000028104',
            'XML_ID' => 'IR27',
        ],
        [
            'NAME'   => 'Амурская область',
            'CODE'   => '0000028105',
            'XML_ID' => 'IR28',
        ],
        [
            'NAME'   => 'Камчатский край',
            'CODE'   => '0000028106',
            'XML_ID' => 'IR41',
        ],
        [
            'NAME'   => 'Магаданская область',
            'CODE'   => '0000028107',
            'XML_ID' => 'IR49',
        ],
        [
            'NAME'   => 'Республика Саха (Якутия)',
            'CODE'   => '0000028108',
            'XML_ID' => 'IR14',
        ],
        [
            'NAME'   => 'Приморский край',
            'CODE'   => '0000028109',
            'XML_ID' => 'IR25',
        ],
        [
            'NAME'   => 'Сахалинская область',
            'CODE'   => '0000028110',
            'XML_ID' => 'IR65',
        ],
        [
            'NAME'   => 'Еврейская автономная область',
            'CODE'   => '0000028111',
            'XML_ID' => 'IR79',
        ],
        [
            'NAME'   => 'Чукотский автономный округ',
            'CODE'   => '0000028112',
            'XML_ID' => 'IR87',
        ],
        [
            'NAME'   => 'Крым',
            'CODE'   => '0000028114',
            'XML_ID' => 'IR91',
        ],
        [
            'NAME'   => 'Москва',
            'CODE'   => '0000073738',
            'XML_ID' => 'IR77',
        ],
        [
            'NAME'   => 'Санкт-Петербург',
            'CODE'   => '0000103664',
            'XML_ID' => 'IR78',
        ],
        [
            'NAME'   => 'Севастополь',
            'CODE'   => '0001092542',
            'XML_ID' => 'IR92',
        ],
    ];

    public function up()
    {
        /** @var LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');

        if ($externalService = ExternalServiceTable::getList(
            [
                'filter' => ['CODE' => static::EXTERNAL_CODE],
            ]
        )->fetch()) {
            $externalServiceId = $externalService['ID'];
            $this->log()->info('Внешний сервис местоположений ' . static::EXTERNAL_CODE . ' уже существует');
        } else {
            $addResult = ExternalServiceTable::add(['CODE' => static::EXTERNAL_CODE]);
            if ($addResult->isSuccess()) {
                $externalServiceId = $addResult->getId();
                $this->log()->info('Внешний сервис местоположений ' . static::EXTERNAL_CODE . ' добавлен');
            } else {
                $this->log()->error(
                    'Ошибка при добавлении внешнего сервиса местоположений ' . static::EXTERNAL_CODE
                );

                return false;
            }
        }

        $regionCodes = array_column($this->regions, 'CODE');

        $locations = LocationTable::getList(
            [
                'filter' => [
                    'CODE' => $regionCodes,
                ],
                'select' => ['ID', 'CODE'],
            ]
        );

        $codeToId = [];
        while ($location = $locations->fetch()) {
            $codeToId[$location['CODE']] = $location['ID'];
        }

        foreach ($this->regions as $region) {
            if (!$id = $codeToId[$region['CODE']]) {
                $this->log()->warning('Регион ' . $region['NAME'] . ' не найден');
                continue;
            }

            ExternalTable::add(
                [
                    'LOCATION_ID' => $id,
                    'SERVICE_ID'  => $externalServiceId,
                    'XML_ID'      => $region['XML_ID'],
                ]
            );
        }

        return true;
    }

    public function down()
    {
        if (!$externalService = ExternalServiceTable::getList(
            [
                'filter' => ['CODE' => static::EXTERNAL_CODE],
            ]
        )->fetch()) {
            $this->log()->info('Внешний сервис местоположений ' . static::EXTERNAL_CODE . ' не найден');

            return false;
        }

        $externalServiceId = $externalService['ID'];

        $items = ExternalTable::getList(['filter' => ['SERVICE_ID' => $externalServiceId]]);
        while ($item = $items->fetch()) {
            ExternalTable::delete($item['ID']);
        }

        if (ExternalServiceTable::delete($externalServiceId)) {
            $this->log()->info('Внешний сервис местоположений ' . static::EXTERNAL_CODE . ' удален');
        } else {
            $this->log()->error(
                'Ошибка при удалении внешнего сервиса местоположений ' . static::EXTERNAL_CODE
            );

            return false;
        }

        return true;
    }
}
