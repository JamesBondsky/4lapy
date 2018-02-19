<?php

namespace FourPaws\Components;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Product;
use FourPaws\CatalogBundle\Service\DiscountMarkService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection
 *
 * Class CatalogElementSnippet
 */
class CatalogElementSnippet extends \CBitrixComponent
{
    /**
     * @var DiscountMarkService
     */
    private $discountMarkService;

    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->discountMarkService = Application::getInstance()->getContainer()->get(DiscountMarkService::class);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['PRODUCT'] = $params['PRODUCT'] ?? null;
        $params['PRODUCT'] = $params['PRODUCT'] instanceof Product ? $params['PRODUCT'] : null;

        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 360000;
        $params['CACHE_TYPE'] = $params['CACHE_TIME'] === 0 ? 'N' : $params['CACHE_TYPE'];

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @return void
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public function executeComponent()
    {
        if ($this->startResultCache($this->arParams['CACHE_TIME'])) {
            parent::executeComponent();

            if ($this->arParams['PRODUCT']) {
                $this->arResult['PRODUCT'] = $this->arParams['PRODUCT'];

                $this->includeComponentTemplate();
                return;
            }

            $this->abortResultCache();
        }
    }

    /**
     * @return DiscountMarkService
     */
    public function getDiscountMarkService(): DiscountMarkService
    {
        return $this->discountMarkService;
    }
}
