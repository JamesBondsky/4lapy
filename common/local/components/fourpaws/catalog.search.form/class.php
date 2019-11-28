<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Context;
use FourPaws\App\Application;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/** @noinspection AutoloadingIssuesInspection */

class FourPawsCatalogSearchFormComponent extends \CBitrixComponent
{

    /** {@inheritdoc} */
    public function onPrepareComponentParams($params): array
    {
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? getenv('GLOBAL_CACHE_TTL');
        return $params;
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            if ($this->startResultCache()) {
                $this->prepareResult();

                $this->includeComponentTemplate();
            }
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (RuntimeException $e) {
            }
        }
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function prepareResult(): self
    {
        /** @var Router */
        $router = Application::getInstance()->getContainer()->get('router');
        /** @var Symfony\Component\Routing\RouteCollection $routes */
        $routes = $router->getRouteCollection();

        $route = $routes->get('fourpaws_catalog_ajax_search_autocomplete');
        if (!$route) {
            $this->abortResultCache();
            throw new RuntimeException('Catalog autocomplete route not found');
        }
        $this->arResult['AUTOCOMPLETE_URL'] = $route->getPath();

        $route = $routes->get('fourpaws_catalog_catalog_search');
        if (!$route) {
            $this->abortResultCache();
            throw new RuntimeException('Catalog search route not found');
        }
        $this->arResult['SEARCH_URL'] = $route->getPath();

        $this->arResult['QUERY'] = Context::getCurrent()->getRequest()->get('query');

        return $this;
    }
}
