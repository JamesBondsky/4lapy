<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsCitySelectorComponent extends \CBitrixComponent
{

    /** {@inheritdoc} */
    public function onPrepareComponentParams($params): array
    {
        return $params;
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $this->prepareResult();

            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }

    /**
     * @return $this
     */
    protected function prepareResult()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router */
        $router = Application::getInstance()->getContainer()->get('router');
        /** @var Symfony\Component\Routing\RouteCollection $routes */
        $routes = $router->getRouteCollection();

        /** @var \FourPaws\Location\LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');
        /** @var \FourPaws\UserBundle\Service\UserService $userService */
        $userService = Application::getInstance()
                                  ->getContainer()
                                  ->get('FourPaws\UserBundle\Service\UserCitySelectInterface');

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

        $this->arResult['SELECTED_CITY'] = $userService->getSelectedCity();

        return $this;
    }
}
