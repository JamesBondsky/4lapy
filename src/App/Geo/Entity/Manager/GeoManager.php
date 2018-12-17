<?php

namespace FourPaws\App\Geo\Entity\Manager;

use Bitrix\Main\Entity\ExpressionField;
use FourPaws\App\Geo\Entity\Table\CityTable;
use FourPaws\App\Geo\Geo;

class GeoManager
{

    private
        $cityTable;

    public function __construct()
    {
        $this->cityTable = new CityTable();
    }

    /**
     * @param $id
     * @return bool
     */
    public function getCityById($id)
    {
        $arCities = $this->cityTable->getList([
            'select' => ['id', 'name_ru', 'region.id', 'region.name_ru'],
            'filter' => [
                'id' => $id,
            ],
        ])->fetch();
        return $arCities ?: false;
    }

    public function getCitiesByString($string)
    {

        $arCities = $this->cityTable->getList([
            'select' => ['id', 'name_ru', 'region.id', 'region.name_ru'],
            'filter' => [
                'name_ru' => '%' . mb_strtolower($string, 'UTF-8') . '%',
                'region.country' => 'RU',
            ],
            'order' => ['name_ru' => 'asc'],
        ])->fetchAll();

        return $arCities;
    }

    public function getCurrentCityFiasId()
    {
        return $this->getCityById(Geo::getInstance()->getCityId())['fias'];
    }

}