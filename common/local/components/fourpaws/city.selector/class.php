<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\RouteCollection;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsCitySelectorComponent extends \CBitrixComponent
{
    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * {@inheritdoc}
     */
    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000000;
        }

        $params['LOCATION_CODE'] = $params['LOCATION_CODE'] ?? null;

        return $params;
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * {@inheritdoc}
     */
    public function executeComponent()
    {
        try {
            $this->prepareResult();

            $this->includeComponentTemplate();
        } catch (Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (RuntimeException $e) {}
        }
    }

    /**
     * @return $this
     *
     * @throws Exception
     * @throws IblockNotFoundException
     * @throws ApplicationCreateException
     */
    protected function prepareResult()
    {
        /** @var Router */
        $router = Application::getInstance()->getContainer()->get('router');
        /** @var RouteCollection $routes */
        $routes = $router->getRouteCollection();

        /** @var \FourPaws\LocationBundle\LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');
        /** @var \FourPaws\UserBundle\Service\UserService $userService */
        $userService = Application::getInstance()
                                  ->getContainer()
                                  ->get(UserCitySelectInterface::class);

        if ($citySetRoute = $routes->get('fourpaws_user_ajax_city_set')) {
            $this->arResult['CITY_SET_URL'] = $citySetRoute->getPath();
        }
        if ($cityGetRoute = $routes->get('fourpaws_user_ajax_city_get')) {
            $this->arResult['CITY_GET_URL'] = $cityGetRoute->getPath();
        }
        if ($cityAutocompleteRoute = $routes->get('location.city.autocomplete')) {
            $this->arResult['CITY_AUTOCOMPLETE_URL'] = $cityAutocompleteRoute->getPath();
        }

        $availableCities = $locationService->getAvailableCities();

        $this->arResult['POPULAR_CITIES'] = $availableCities['popular'] ?? [];
        $this->arResult['MOSCOW_CITIES'] = $availableCities['moscow_region'] ?? [];

        $this->arResult['DEFAULT_CITY'] = $locationService->getDefaultLocation();

        $this->arResult['SELECTED_CITY'] = $this->arParams['LOCATION_CODE']
            ? $locationService->findLocationByCode($this->arParams['LOCATION_CODE'])
            : $userService->getSelectedCity();

        return $this;
    }
}
