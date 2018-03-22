<?php

namespace FourPaws\Components;

use Bitrix\Main\Application as BitrixApplication;
use CBitrixComponent;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Templates\MediaEnum;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\CatalogBundle\Service\MarkService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection
 *
 * Class CatalogElementSnippet
 */
class CatalogElementSnippet extends CBitrixComponent
{
    /**
     * @var MarkService
     */
    private $markService;

    /**
     * CatalogElementSnippet constructor.
     *
     * @param CBitrixComponent $component
     *
     * @throws ApplicationCreateException
     */
    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->markService = Application::getInstance()->getContainer()->get(MarkService::class);
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

        $params['NOT_CATALOG_ITEM_CLASS'] = $params['NOT_CATALOG_ITEM_CLASS'] ?? 'N';

        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?? 360000;
        $params['CACHE_TYPE'] = $params['CACHE_TIME'] === 0 ? 'N' : $params['CACHE_TYPE'];

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @return void
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public function executeComponent()
    {
        if ($this->startResultCache($this->arParams['CACHE_TIME'])) {
            parent::executeComponent();

            if ($this->arParams['PRODUCT']) {
                /** @var Product $product */
                $this->arResult['PRODUCT'] = $product = $this->arParams['PRODUCT'];

                if (!empty($this->arParams['CURRENT_OFFER']) && $this->arParams['CURRENT_OFFER'] instanceof Offer) {
                    $currentOffer = $this->arParams['CURRENT_OFFER'];
                } else {
                    /**
                     * @todo Завязать текущий оффер на фильтр.
                     */
                    $offers = $product->getOffers();
                    $currentOffer = null;
                    foreach ($offers as $offer) {
                        if ($offer->getImages()->count() >= 1 && $offer->getImages()->first() !== MediaEnum::NO_IMAGE_WEB_PATH) {
                            $currentOffer = $offer;
                        }
                    }

                    if (!($currentOffer instanceof Offer)) {
                        $currentOffer = $offers->first();
                    }
                }

                $this->arResult['CURRENT_OFFER'] = $currentOffer;

                $this->includeComponentTemplate();

                if (\defined('BX_COMP_MANAGED_CACHE')) {
                    $instance = BitrixApplication::getInstance();
                    $tagCache = $instance->getTaggedCache();
                    $tagCache->registerTag('catalog:offer:' . $currentOffer->getId());
                    $tagCache->registerTag('catalog:product:' . $product->getId());
                }
                return;
            }

            $this->abortResultCache();
        }
    }

    /**
     * @return MarkService
     */
    public function getMarkService(): MarkService
    {
        return $this->markService;
    }
}
