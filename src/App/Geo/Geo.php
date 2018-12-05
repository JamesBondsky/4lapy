<?php

namespace FourPaws\App\Geo;

use Bitrix\Main\Application;
use Bitrix\Main\Web\Cookie;
use IgI\SypexGeo\SxGeo;
use FourPaws\App\Geo\Entity\Manager\GeoManager;

class Geo
{

    const COOKIE_CITY_ID = 'SX_CITY_ID';

    const
        CITY_MOSCOW = 524901,
        CITY_SAINT_PETERSBURG = 498817,
        CITY_YEKATERINBURG = 1486209,
        CITY_NOVOSIBIRSK = 1496747,

        DEFAULT_CITY_ID = self::CITY_MOSCOW;

    const PARAM_IP = 'ip';

    private
        $cityName,
        $cityId,
        $region,
        $regionName;

    /**
     * @return mixed
     */
    public function getRegionName()
    {
        return $this->regionName;
    }

    /**
     * @param mixed $regionName
     */
    public function setRegionName($regionName)
    {
        $this->regionName = $regionName;
    }

    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param mixed $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
        return $this;
    }

    private
        $obRequest,
        $obResponse,
        $obGeoManager;

    /**
     * @return mixed
     */
    public function getCityName()
    {
        return $this->cityName;
    }

    /**
     * @param mixed $cityName
     * @return $this
     */
    public function setCityName($cityName)
    {
        $this->cityName = $cityName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * @param int $cityId
     * @return $this
     */
    public function setCityId($cityId)
    {
        $this->obResponse->addCookie(new Cookie($this::COOKIE_CITY_ID, '', time() - 500));

        $cookie = new Cookie($this::COOKIE_CITY_ID, $cityId);
        $cookie->setDomain(Application::getInstance()->getContext()->getServer()->getHttpHost());
        $this->obResponse->addCookie($cookie);
        $this->cityId = $cityId;
        return $this;
    }

    public function setCityFromCookie()
    {
        $cookieCityId = (int)$this->obRequest->getCookie($this::COOKIE_CITY_ID);

        if ($city = $this->obGeoManager->getCityById($cookieCityId)) {
            $this->setCityId($city['id']);
            $this->setCityName($city['name_ru']);
            $this->setRegion($city['CORE_GEO_ENTITY_TABLE_CITY_region_id']);
            $this->setRegionName($city['CORE_GEO_ENTITY_TABLE_CITY_region_name_ru']);

            return true;
        }

        return false;
    }

    public function setDefaultCity()
    {
        $city = $this->obGeoManager->getCityById($this::DEFAULT_CITY_ID);
        $this->setCityId($city['id']);
        $this->setCityName($city['name_ru']);
        $this->setRegion($city['CORE_GEO_ENTITY_TABLE_CITY_region_id']);
        $this->setRegionName($city['CORE_GEO_ENTITY_TABLE_CITY_region_name_ru']);
    }

    public function setCityFromId($id)
    {
        if ($city = $this->obGeoManager->getCityById($id)) {
            $this->setCityId($city['id']);
            $this->setCityName($city['name_ru']);
            $this->setRegion($city['CORE_GEO_ENTITY_TABLE_CITY_region_id']);
            $this->setRegionName($city['CORE_GEO_ENTITY_TABLE_CITY_region_name_ru']);
            return true;
        }

        return false;
    }

    public function setCityFromSxgeo()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?: false;

        if ($ip) {
            /** выставляем кодировку для работы с базой sxgeo (опять битрикс своим mbstring.func_overload поднасрал!!!) */
            $encoding = mb_internal_encoding();
            mb_internal_encoding('8bit');

            $obSxGeo = (new SxGeo($_SERVER['DOCUMENT_ROOT'] . '/local/resources/sypexgeo/SxGeoCity.dat'));
            $geoData = $obSxGeo->get($ip);
            /** возвращаем старую кодировку */
            mb_internal_encoding($encoding);

            if ($geoData) {
                if ($geoData['country']['iso'] == 'RU') {
                    $this->setCityName($geoData['city']['name_ru']);
                    $this->setCityId($geoData['city']['id']);
                    $this->setRegion($geoData['region']['id']);
                    $this->setRegion($geoData['region']['name_ru']);
                }
            }
        }
    }

    public function __construct()
    {

        $this->obRequest = Application::getInstance()->getContext()->getRequest();
        $this->obResponse = Application::getInstance()->getContext()->getResponse();
        $this->obGeoManager = new GeoManager();

        $this->loadCityName();
    }

    private function loadCityName()
    {

        if (!$this->obRequest->getCookie($this::COOKIE_CITY_ID)) {

            $this->setCityFromSxgeo();
        } else {
            $this->setCityFromCookie();
        }

        if (!$this->getCityName()) {
            $this->setDefaultCity();
        }

    }

}