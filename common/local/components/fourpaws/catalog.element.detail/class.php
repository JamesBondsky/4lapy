<?php

namespace FourPaws\Components;

use Bitrix\Catalog\CatalogViewedProductTable;
use Bitrix\Catalog\Product\Basket;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Iblock\InheritedProperty\ElementValues;
use Bitrix\Main\Analytics\Catalog;
use Bitrix\Main\Analytics\Counter;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Text\JsExpression;
use CBitrixComponent;
use Exception;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Templates\MediaEnum;
use FourPaws\BitrixOrm\Collection\ImageCollection;
use FourPaws\BitrixOrm\Model\Exceptions\CatalogProductNotFoundException;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\EcommerceBundle\Service\RetailRocketService;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use WebArch\BitrixCache\BitrixCache;

/** @noinspection AutoloadingIssuesInspection EfferentObjectCouplingInspection
 *
 * Class CatalogElementDetailComponent
 *
 * @package FourPaws\Components
 */
class CatalogElementDetailComponent extends \CBitrixComponent
{
    public const EXPAND_CLOSURES = 'EXPAND_CLOSURES';

    protected $unionOffers = [];

    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;
    /**
     * @var GoogleEcommerceService
     */
    private $ecommerceService;
    /**
     * @var BasketService
     */
    private $basketService;
    /**
     * @var RetailRocketService
     */
    private $retailRocketService;

    /**
     * CatalogElementDetailComponent constructor.
     *
     * @param CBitrixComponent|null $component
     *
     * @throws RuntimeException
     * @throws SystemException
     */
    public function __construct(?CBitrixComponent $component = null)
    {
        try {
            $container = App::getInstance()->getContainer();
            $this->ecommerceService = $container->get(GoogleEcommerceService::class);
            $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
            $this->basketService = $container->get(BasketService::class);
            $this->retailRocketService = $container->get(RetailRocketService::class);
        } catch (ApplicationCreateException | ServiceCircularReferenceException | ServiceNotFoundException $e) {
        }

        parent::__construct($component);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 36000000;
        }

        $params['SHOW_FAST_ORDER'] = $params['SHOW_FAST_ORDER'] ?? false;
        $params['CODE'] = $params['CODE'] ?? '';
        $params['OFFER_ID'] = $params['OFFER_ID'] ?? 0;
        $params['SET_TITLE'] = ($params['SET_TITLE'] === 'Y') ? $params['SET_TITLE'] : 'N';
        $params['SET_VIEWED_IN_COMPONENT'] = $params['SET_VIEWED_IN_COMPONENT'] ?? 'Y';

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @return mixed
     *
     * @throws RuntimeException
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws SystemException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public function executeComponent()
    {
        if (!$this->arParams['CODE']) {
            Tools::process404([], true, true, true);
        }

        parent::executeComponent();

        if ($this->startResultCache()) {
            /** @var Product $product */
            try {
                $product = $this->getProduct($this->arParams['CODE']);
            } catch (CatalogProductNotFoundException $e) {
                $product = false;
            }

            if (!$product) {
                $this->abortResultCache();
                Tools::process404([], true, true, true);

                return false;
            }

            $currentOffer = $this->getCurrentOffer($product, (int)$this->arParams['OFFER_ID']);

            TaggedCacheHelper::addManagedCacheTags([
                'iblock:item:' . $product->getId(),
            ]);

            $sectionId = (int)current($product->getSectionsIdList());

            $offersXmlIds = [];

            foreach (['flavour', 'color'] as $combination) {
                if (count($offersXmlIds) < 2) {
                    $propVal = ($combination == 'flavour') ? $currentOffer->getFlavourCombination() : $currentOffer->getColourCombination();
                    if ($propVal != '' && $propVal != null) {
                        $unionOffers = $this->getOffersByUnion($combination, $propVal);
                        if (!$unionOffers->isEmpty()) {
                            /** @var Offer $unionOffer */
                            foreach ($unionOffers as $unionOffer) {
                                $offerXmlID = $unionOffer->getXmlId();
                                if (!in_array($offerXmlID, $offersXmlIds)) {
                                    $offersXmlIds[] = $offerXmlID;
                                }
                            }
                            ksort($offersXmlIds);
                        }
                    }
                }
            }

            if (count($offersXmlIds) < 2) {
                /** @var Offer $offer */
                foreach ($product->getOffersSorted() as $offer) {
                    $offersXmlIds[] = $offer->getXmlId();
                }
            }

            $this->arResult = [
                'PRODUCT'               => $product,
                'CURRENT_OFFER'         => $currentOffer,
                'SECTION_CHAIN'         => $this->getSectionChain($sectionId),
                'SHOW_FAST_ORDER'       => $this->arParams['SHOW_FAST_ORDER'],
                'ECOMMERCE_VIEW_SCRIPT' => \sprintf(
                    "<script>%s\n%s</script>",
                    $this->ecommerceService->renderScript(
                        $this->ecommerceService->buildDetailFromOffer($currentOffer, 'Карточка товара')
                    ),
                    $this->retailRocketService->renderDetailView('[' . implode(',', $offersXmlIds) . ']')
                ),
                'BASKET_LINK_EVENT'     => \sprintf(
                    'onmousedown="%s"',
                    $this->retailRocketService->renderAddToBasket($currentOffer->getXmlId())
                ),
                'OFFERS' => $product->getOffersSorted(),
                'BRAND' => $product->getBrand()
            ];

            foreach ($this->arResult['OFFERS'] as &$offer) {
                $imagesIDs = $offer->getImagesIds();
                if (!$imagesIDs) {
                    $imageCollection = ImageCollection::createFromIds($imagesIDs);
                    $offer->withImages($imageCollection);
                    $offer->getResizeImages(480, 480);
                }
            }

            $this->setResultCacheKeys([
                'PRODUCT',
                'CURRENT_OFFER',
                'SHOW_FAST_ORDER',
                'OFFERS',
                'BRAND'
            ]);

            $this->includeComponentTemplate();
        }


        $this->setSeo($this->arResult['CURRENT_OFFER']);

        // bigdata
        $this->obtainCounterData();
        $this->sendCounters();
        $this->saveViewedProduct();

        return $this->arResult['PRODUCT'];
    }

    /**
     * @param Offer $offer
     *
     * @return $this
     *
     * @throws Exception
     */
    public function setSeo(?Offer $offer): self
    {
        if (!$offer) {
            return $this;
        }

        global $APPLICATION;

        $cache = (new BitrixCache())
            ->withTag(\sprintf(
                'iblock:item:%d',
                $offer->getId()
            ))
            ->withId(__METHOD__ . $offer->getId())
            ->withTime($this->arParams['CACHE_TIME']);

        $properties = $cache->resultOf(function () use ($offer) {
            return \array_map(function ($meta) use ($offer) {
                return \str_replace(
                    [
                        '#BRAND#',
                        '#NAME#',
                        '#PRICE#'
                    ],
                    [
                        $offer->getProduct()
                              ->getBrandName(),
                        $offer->getName(),
                        $offer->getCatalogPrice()
                    ],
                    $meta
                );
            }, (new ElementValues($offer->getIblockId(), $offer->getId()))->getValues());
        });

        $APPLICATION->SetTitle($properties['ELEMENT_META_TITLE']);
        $APPLICATION->SetPageProperty('description', $properties['ELEMENT_META_DESCRIPTION']);
        $APPLICATION->SetPageProperty(
            'canonical',
            (new FullHrefDecorator($offer->getProduct()->getDetailPageUrl()))->__toString()
        );

        return $this;
    }

    /**
     * @param string $type
     * @param string $val
     *
     * @return OfferCollection
     */
    public function getOffersByUnion(string $type, string $val): OfferCollection
    {
        if (!isset($this->unionOffers[$type][$val])) {
            $offerCollection = null;
            switch ($type) {
                case 'color':
                    $offerCollection = (new OfferQuery())->withFilter(['PROPERTY_COLOUR_COMBINATION' => $val])
                                                         ->exec();
                    break;
                case 'flavour':
                    $offerCollection = (new OfferQuery())->withFilter(['PROPERTY_FLAVOUR_COMBINATION' => $val])
                                                         ->exec();
                    break;
            }
            if (null !== $offerCollection) {
                $this->unionOffers[$type][$val] = $offerCollection;
            }

        }

        return $this->unionOffers[$type][$val];
    }

    /**
     * @return UserService
     */
    public function getCurrentUserService(): UserService
    {
        return $this->currentUserProvider;
    }

    /**
     * @param string $code
     *
     * @return Product
     * @throws CatalogProductNotFoundException
     */
    protected function getProduct(string $code): Product
    {
        $res = (new ProductQuery())
            ->withFilterParameter('CODE', $code)
            ->exec();
        if ($res->count() === 0) {
            throw new CatalogProductNotFoundException('Товар по коду не найден');
        }

        return $res->first();
    }

    /**
     * @param int $sectionId
     *
     * @return array
     */
    protected function getSectionChain(int $sectionId): array
    {
        $sectionChain = [];
        if ($sectionId > 0) {
            $items = \CIBlockSection::GetNavChain(false, $sectionId, [
                'ID',
                'NAME'
            ]);
            /** @noinspection PhpAssignmentInConditionInspection */
            while ($item = $items->getNext(true, false)) {
                $sectionChain[] = $item;
            }
        }

        return $sectionChain;
    }

    /**
     * @return BasketService
     */
    public function getBasketService(): BasketService
    {
        return $this->basketService;
    }

    /**
     * @param int $sectionId
     *
     * @return null|Category
     */
    protected function getSection(int $sectionId): ?Category
    {
        if ($sectionId <= 0) {
            // не экзепшен?
            return null;
        }

        return (new CategoryQuery())
            ->withFilterParameter('ID', $sectionId)
            ->exec()
            ->first();
    }

    /**
     * Добавление в просмотренные товары при генерации результата
     *
     * @throws ObjectNotFoundException
     * @throws NotSupportedException
     * @throws LoaderException
     */
    protected function saveViewedProduct(): void
    {
        if ($this->arParams['SET_VIEWED_IN_COMPONENT'] === 'Y' && !empty($this->arResult['PRODUCT'])) {
            // задано действие добавления в просмотренные при генерации результата
            // (в идеале это нужно делать черех ajax)
            if (Basket::isNotCrawler()) {
                /** @var Product $product */
                $product = $this->arResult['PRODUCT'];
                $currentOffer = $product->getOffers()
                                        ->first();
                $parentId = $product->getId();
                $productId = $currentOffer ? $currentOffer->getId() : 0;
                $productId = $productId > 0 ? $productId : $parentId;

                CatalogViewedProductTable::refresh(
                    $productId,
                    \CSaleBasket::GetBasketUserID(),
                    $this->getSiteId(),
                    $parentId
                );
            }
        }
    }

    /**
     * Получение данных для BigData
     *
     * @return void
     * @throws ObjectNotFoundException
     * @throws NotSupportedException
     * @throws LoaderException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentNullException
     */
    protected function obtainCounterData(): void
    {
        if (empty($this->arResult['PRODUCT'])) {
            return;
        }
        /** @var Product $product */
        $product = $this->arResult['PRODUCT'];

        $categoryId = '';
        $categoryPath = [];
        if ($this->arResult['SECTION_CHAIN']) {
            foreach ($this->arResult['SECTION_CHAIN'] as $cat) {
                $categoryPath[$cat['ID']] = $cat['NAME'];
                $categoryId = $cat['ID'];
            }
        }

        $counterData = [
            'product_id'    => $product->getId(),
            'iblock_id'     => $product->getIblockId(),
            'product_title' => $product->getName(),
            'category_id'   => $categoryId,
            'category'      => $categoryPath,
        ];

        $currentOffer = $this->getCurrentOffer($product, (int)$this->arParams['OFFER_ID']);
        $counterData['price'] = $currentOffer ? $currentOffer->getPrice() : 0;
        $counterData['currency'] = $currentOffer ? $currentOffer->getCurrency() : '';

        // make sure it is in utf8
        $counterData = Encoding::convertEncoding($counterData, SITE_CHARSET, 'UTF-8');

        // pack value and protocol version
        $rcmLogCookieName = Option::get('main', 'cookie_name',
                'BITRIX_SM') . '_' . Catalog::getCookieLogName();

        $this->arResult['counterDataSource'] = $counterData;
        $this->arResult['counterData'] = [
            'item'           => base64_encode(json_encode($counterData)),
            'user_id'        => new JsExpression(
                'function(){return BX.message("USER_ID") ? BX.message("USER_ID") : 0;}'
            ),
            'recommendation' => new JsExpression(
                'function() {
                    var rcmId = "";
                    var cookieValue = BX.getCookie("' . $rcmLogCookieName . '");
                    var productId = ' . $product->getId() . ';
                    var cItems = [];
                    var cItem;

                    if (cookieValue)
                    {
                        cItems = cookieValue.split(".");
                    }

                    var i = cItems.length;
                    while (i--)
                    {
                        cItem = cItems[i].split("-");
                        if (cItem[0] == productId)
                        {
                            rcmId = cItem[1];
                            break;
                        }
                    }

                    return rcmId;
                }'
            ),
            'v'              => '2',
        ];
    }

    /**
     * Отправка bigdata
     *
     * @return void
     */
    protected function sendCounters(): void
    {
        if (isset($this->arResult['counterData']) && Catalog::isOn()) {
            Counter::sendData('ct', $this->arResult['counterData']);
        }
    }

    /**
     * @param Product $product
     * @param int     $offerId
     *
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     *
     * @return Offer
     */
    public function getCurrentOffer(Product $product, int $offerId = 0): Offer
    {
        if ($offerId > 0) {
            $offer = OfferQuery::getById($offerId);
            if ($offer === null) {
                $offers = $product->getOffers();
            }
        } else {
            $offers = $product->getOffers();
            foreach ($offers as $offer) {
                if ($offer->getImages()
                          ->count() >= 1
                    && $offer->getImages()
                             ->first() !== MediaEnum::NO_IMAGE_WEB_PATH) {
                    break;
                }
            }
        }

        return $offer ?? $offers->last();
    }
}
