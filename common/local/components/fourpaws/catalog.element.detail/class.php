<?php

namespace FourPaws\Components;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Catalog\CatalogViewedProductTable;
use Bitrix\Catalog\Product\Basket;
use Bitrix\Iblock\Component\Tools;
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
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Templates\MediaEnum;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection EfferentObjectCouplingInspection
 *
 * Class CatalogElementDetailComponent
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
     * CatalogElementDetailComponent constructor.
     *
     * @param CBitrixComponent|null $component
     *
     * @throws \RuntimeException
     * @throws SystemException
     */
    public function __construct(?CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();
            $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        } catch (ApplicationCreateException | ServiceCircularReferenceException | ServiceNotFoundException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
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

        if ($this->startResultCache()) {
            parent::executeComponent();

            /** @var Product $product */
            $product = $this->getProduct($this->arParams['CODE']);
            $currentOffer = $this->getCurrentOffer($product, (int)$this->arParams['OFFER_ID']);

            if (!$product) {
                $this->abortResultCache();
                Tools::process404([], true, true, true);
            }

            $sectionId = (int)current($product->getSectionsIdList());

            $this->arResult = [
                'PRODUCT' => $product,
                'CURRENT_OFFER' => $currentOffer,
                'SECTION_CHAIN' => $this->getSectionChain($sectionId),
                'SHOW_FAST_ORDER' => $this->arParams['SHOW_FAST_ORDER'],
                /**
                 * @todo впилить seo
                 */
                // возможно, понадобится в будущем
                //'SECTION' => $this->getSection($sectionId),
            ];

            TaggedCacheHelper::addManagedCacheTags([
                'catalog:offer:' . $currentOffer->getId(),
                'catalog:product:' . $product->getId(),
                'iblock:item:' . $product->getId(),
            ]);

            $this->setResultCacheKeys(['PRODUCT', 'CURRENT_OFFER', 'SHOW_FAST_ORDER']);

            $this->includeComponentTemplate();
        }

        // bigdata
        $this->obtainCounterData();
        $this->sendCounters();
        $this->setMeta();
        $this->saveViewedProduct();

        return $this->arResult['PRODUCT'];
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
                    $offerCollection = (new OfferQuery())->withFilter(['PROPERTY_COLOUR_COMBINATION' => $val])->exec();
                    break;
                case 'flavour':
                    $offerCollection = (new OfferQuery())->withFilter(['PROPERTY_FLAVOUR_COMBINATION' => $val])->exec();
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
     */
    protected function getProduct(string $code): Product
    {
        return (new ProductQuery())
            ->withFilterParameter('CODE', $code)
            ->exec()
            ->first();
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
            $items = \CIBlockSection::GetNavChain(false, $sectionId, ['ID', 'NAME']);
            /** @noinspection PhpAssignmentInConditionInspection */
            while ($item = $items->getNext(true, false)) {
                $sectionChain[] = $item;
            }
        }

        return $sectionChain;
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
                $currentOffer = $product->getOffers()->first();
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
            'product_id' => $product->getId(),
            'iblock_id' => $product->getIblockId(),
            'product_title' => $product->getName(),
            'category_id' => $categoryId,
            'category' => $categoryPath,
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
            'item' => base64_encode(json_encode($counterData)),
            'user_id' => new JsExpression(
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
            'v' => '2',
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
     * @todo from inheritedProperties
     */
    protected function setMeta(): void
    {
        global $APPLICATION;

        if ($this->arParams['SET_TITLE'] === 'Y') {
            $APPLICATION->SetTitle($this->arResult['PRODUCT']->getName());
        }
    }

    /**
     * @param Product $product
     * @param int $offerId
     *
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     *
     * @return Offer
     */
    public function getCurrentOffer(Product $product, int $offerId = 0): Offer
    {
        $offers = $product->getOffers();
        if ($offerId > 0) {
            foreach ($offers as $offer) {
                if ($offer->getId() === $offerId) {
                    break;
                }
            }
        } else {
            foreach ($offers as $offer) {
                if ($offer->getImages()->count() >= 1 && $offer->getImages()->first() !== MediaEnum::NO_IMAGE_WEB_PATH) {
                    break;
                }
            }
        }

        return $offer ?? $offers->last();
    }
}
