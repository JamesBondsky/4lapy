<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsCatalogSearchFormComponent extends \CBitrixComponent
{

    /** {@inheritdoc} */
    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000000;
        }

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

        $route = $routes->get('fourpaws_catalog_ajax_search_autocomplete');
        if (!$route) {
            $this->abortResultCache();
            throw new Exception('Catalog search route not found');
        }

        $this->arResult['SEARCH_URL'] = $route->getPath();

        return $this;
    }
}
