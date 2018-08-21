<?php

namespace FourPaws\Components;

use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use CBitrixComponent;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\Helpers\TaggedCacheHelper;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection
 *
 * Class CatalogElementSnippet
 */
class CatalogElementSnippet extends CBitrixComponent
{
    /**
     * @var GoogleEcommerceService
     */
    private $ecommerceService;

    /**
     * CatalogElementSnippet constructor.
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     *
     * @param CBitrixComponent $component
     */
    public function __construct($component)
    {
        $container = Application::getInstance()->getContainer();
        $this->ecommerceService = $container->get(GoogleEcommerceService::class);

        parent::__construct($component);
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

        $params['OFFER_FILTER'] = $params['OFFER_FILTER'] ?? [];
        $params['SHARE_ID'] = $params['SHARE_ID'] ?? 0;
        $params['SHARE_ID'] = (int)$params['SHARE_ID'];

        $params['GOOGLE_ECOMMERCE_TYPE'] = (string)$params['GOOGLE_ECOMMERCE_TYPE'] ?: 'Каталог';

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @return void
     *
     * @throws ObjectNotFoundException
     * @throws NotSupportedException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws SystemException
     * @throws LoaderException
     */
    public function executeComponent(): void
    {
        if ($this->startResultCache()) {
            parent::executeComponent();

            if ($this->arParams['PRODUCT']) {
                /** @var Product $product */
                $this->arResult['PRODUCT'] = $product = $this->arParams['PRODUCT'];
                $this->arResult['CURRENT_OFFER'] = $currentOffer = $this->getCurrentOffer($product);
                TaggedCacheHelper::addManagedCacheTags([
                    'iblock:item:' . $currentOffer->getId(),
                    'iblock:item:' . $product->getId(),
                ]);

                $this->includeComponentTemplate();
                return;
            }

            $this->abortResultCache();
        }
    }

    /**
     * @param Product $product
     *
     * @return mixed|null
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    protected function getCurrentOffer(Product $product)
    {
        if (!empty($this->arParams['CURRENT_OFFER']) && $this->arParams['CURRENT_OFFER'] instanceof Offer) {
            $currentOffer = $this->arParams['CURRENT_OFFER'];
        } else {
            $product->getOffers(true, $this->arParams['OFFER_FILTER']);
            $offers = $product->getOffersSorted();
            /** @var Offer $offer */
            $foundOfferWithImages = false;
            $currentOffer = $offers->last();
            foreach ($offers as $offer) {
                if (!$foundOfferWithImages || $offer->getImagesIds()) {
                    $currentOffer = $offer;
                }
            }
        }

        return $currentOffer;
    }

    /**
     * @return GoogleEcommerceService
     */
    public function getEcommerceService(): GoogleEcommerceService
    {
        return $this->ecommerceService;
    }
}
