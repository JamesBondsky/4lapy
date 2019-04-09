<?php

namespace FourPaws\Components;

use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use CBitrixComponent;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\EcommerceBundle\Service\RetailRocketService;
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
     * @var RetailRocketService
     */
    private $retailRocketService;

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
        $this->retailRocketService = $container->get(RetailRocketService::class);

        parent::__construct($component);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['PRODUCT'] = $params['PRODUCT'] instanceof Product ? $params['PRODUCT'] : null;
        $params['CATEGORY'] = $params['CURRENT_CATEGORY'] instanceof Category ? $params['CURRENT_CATEGORY'] : null;

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
                /**
                 * @var Product  $product
                 * @var Category $category
                 */
                $this->arResult['CATEGORY'] = $category = $this->arParams['CATEGORY'];
                $this->arResult['PRODUCT'] = $product = $this->arParams['PRODUCT'];
                $this->arResult['CURRENT_OFFER'] = $currentOffer = $this->getCurrentOffer($product);

                if ($category && $product->getIblockSectionId() !== $category->getId()) {
                    $product->withDetailPageUrl(
                        \sprintf(
                            '%s%s.html',
                            $category->getSectionPageUrl(),
                            $product->getCode()
                        )
                    );
                }

                if (!$currentOffer) {
                    $this->abortResultCache();

                    return;
                }

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
            $currentOffer = $offers->first();
            foreach ($offers as $offer) {
                $offer->setProduct($product);

                if ($offer->getImagesIds()) {
                    $currentOffer = $offer;
                    break;
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

    /**
     * @return RetailRocketService
     */
    public function getRetailRocketService(): RetailRocketService
    {
        return $this->retailRocketService;
    }
}
