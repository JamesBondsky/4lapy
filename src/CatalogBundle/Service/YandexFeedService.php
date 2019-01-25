<?php

namespace FourPaws\CatalogBundle\Service;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException as BitrixArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\CatalogBundle\Dto\Yandex\Category as YandexCategory;
use FourPaws\CatalogBundle\Dto\Yandex\Currency;
use FourPaws\CatalogBundle\Dto\Yandex\DeliveryOption;
use FourPaws\CatalogBundle\Dto\Yandex\Param;
use FourPaws\CatalogBundle\Dto\Yandex\Feed;
use FourPaws\CatalogBundle\Dto\Yandex\Offer as YandexOffer;
use FourPaws\CatalogBundle\Dto\Yandex\Promo;
use FourPaws\CatalogBundle\Dto\Yandex\Purchase;
use FourPaws\CatalogBundle\Dto\Yandex\Product;
use FourPaws\CatalogBundle\Dto\Yandex\Shop;
use FourPaws\CatalogBundle\Exception\ArgumentException;
use FourPaws\CatalogBundle\Exception\OffersIsOver;
use FourPaws\CatalogBundle\Translate\Configuration;
use FourPaws\CatalogBundle\Translate\ConfigurationInterface;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\WordHelper;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use InvalidArgumentException;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class YandexFeedService
 *
 * @package FourPaws\CatalogBundle\Service
 */
class YandexFeedService extends FeedService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    private const MINIMAL_AVAILABLE_IN_RC = 2;

    private $deliveryInfo;
    /**
     * @var StoreService
     */
    private $storeService;
    /**
     * @var Store
     */
    private $rcStock;

    /**
     * YandexFeedService constructor.
     *
     * @param SerializerInterface $serializer
     * @param Filesystem $filesystem
     */
    public function __construct(SerializerInterface $serializer, Filesystem $filesystem, StoreService $storeService)
    {
        parent::__construct($serializer, $filesystem, Feed::class);

        $this->storeService = $storeService;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param int $step
     * @param string $stockID
     *
     * If need to continue, return true. Else - false.
     *
     * @return boolean
     *
     * @throws RuntimeException
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws IOException
     */
    public function process(ConfigurationInterface $configuration, int $step, string $stockID = null): bool
    {
        /**
         * @var Configuration $configuration
         */

        if ($step === 0) {
            $this->clearFeed($this->getStorageKey());

            $feed = new Feed();
            $this
                ->processFeed($feed, $configuration)
                ->processCurrencies($feed, $configuration)
                ->processDeliveryOptions($feed, $configuration, $stockID)
                ->processCategories($feed, $configuration);

            $this->saveFeed($this->getStorageKey(), $feed);
        } else {
            $feed = $this->loadFeed($this->getStorageKey());

            try {
            $this->processOffers($feed, $configuration, $stockID);
            } catch (OffersIsOver $isOver) {
                $feed = $this->loadFeed($this->getStorageKey());
                $feed->getShop()
                    ->setOffset(null);

            $this->processPromos($feed, $configuration, $stockID);

            $this->publicFeed($feed, Application::getAbsolutePath($configuration->getExportFile()));
            $this->clearFeed($this->getStorageKey());

            return false;
            }
        }

        return true;
    }

    /**
     * @param Feed $feed
     * @param Configuration $configuration
     *
     * @return YandexFeedService
     */
    protected function processFeed(Feed $feed, Configuration $configuration): YandexFeedService
    {
        $feed
            ->setDate(new DateTime())
            ->setShop((new Shop())->setName($configuration->getCompanyName())
                ->setCompany($configuration->getCompanyName())
                ->setUrl(\sprintf(
                    'http%s://%s/',
                    $configuration->isHttps() ? 's' : '',
                    $configuration->getServerName()
                )));

        return $this;
    }

    /**
     * @param Feed $feed
     * @param Configuration $configuration
     *
     * @return YandexFeedService
     */
    protected function processCurrencies(Feed $feed, Configuration $configuration): YandexFeedService
    {
        $currencies = new ArrayCollection();
        $xmlData = $configuration->getXmlData();

        /** @noinspection ForeachSourceInspection */
        foreach ($xmlData['CURRENCY'] as $currency => $setting) {
            $currencies->add((new Currency())->setId($currency)
                ->setRate((int)$setting['rate'] ?: 1));
        }

        $feed->getShop()
            ->setCurrencies($currencies);

        return $this;
    }

    /**
     * @param Feed $feed
     * @param Configuration $configuration
     * @param string $stockID
     *
     * @return YandexFeedService
     *
     * @throws RuntimeException
     * @throws IblockNotFoundException
     * @throws IOException
     * @throws OffersIsOver
     * @throws ArgumentException
     */
    protected function processOffers(
        Feed $feed,
        Configuration $configuration,
        string $stockID = null
    ): YandexFeedService {
        $limit = 500;
        $offers = $feed->getShop()
            ->getOffers();

        $offset = $feed->getShop()
            ->getOffset();
        $offset = $offset ?? 0;

        $filter = $this->buildOfferFilter($feed, $configuration);

        if (!empty($stockID)) {
            $filter['>CATALOG_STORE_AMOUNT_' . $stockID] = '2';
        }

        $offerCollection = $this->getOffers($filter, $offset, $limit);

        $this
            ->log()
            ->info(
                \sprintf(
                    'Offers page %d, limit %d, offset %d, pages %d, full count %d',
                    $offerCollection->getCdbResult()->NavPageNomer,
                    $limit,
                    $offset,
                    $offerCollection->getCdbResult()->NavPageCount,
                    $offerCollection->getCdbResult()->NavRecordCount
                )
            );

        foreach ($offerCollection as $k => $offer) {
            ++$offset;

            try {
                $this->addOffer($offer, $offers, $configuration->getServerName(), $stockID);
            } catch (Exception $e) {
                /** Просто подавляем исключение */
            }
        }


        $feed->getShop()
            ->setOffers($offers)
            ->setOffset($offset);
        $this->saveFeed($this->getStorageKey(), $feed);

        $cdbResult = $offerCollection->getCdbResult();
        if ($this->getPageNumber($offset, $limit) === (int)$cdbResult->NavPageCount) {
            throw new OffersIsOver('All offers was been processed.');
        }

        return $this;
    }

    /**
     * @param array $filter
     * @param int $offset
     * @param int $limit
     *
     * @return OfferCollection
     */
    protected function getOffers(array $filter, int $offset = 0, $limit = 500): OfferCollection
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return (new OfferQuery())->withFilter($filter)
            ->withNav([
                'nPageSize' => $limit,
                'iNumPage' => $this->getPageNumber($offset, $limit),
            ])
            ->exec();
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return int
     */
    protected function getPageNumber(int $offset, int $limit): int
    {
        return (int)\ceil(($offset + 1) / $limit);
    }

    /**
     * @param Offer $offer
     * @param ArrayCollection $collection
     * @param string $host
     *
     * @param string|null $stockID
     * @throws ApplicationCreateException
     * @throws BitrixArgumentException
     * @throws DeliveryNotFoundException
     * @throws LoaderException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    public function addOffer(Offer $offer, ArrayCollection $collection, string $host, string $stockID = null): void
    {
        //isOfferExcluded - проверка наличия в складе DC01 и на акционные товары/новинки и прочее
        if (empty($stockID) && $this->isOfferExcluded($offer)) {
            return;
        }

        $url = 'market.yandex.ru';

        if (!empty($stockID)) {
            switch ($stockID) {
                case '47':
                    $url = 'yaroslavl.market.yandex.ru';
                    break;
                case '151':
                    $url = 'voronezh.yandex.ru';
                    break;
                case '168':
                    $url = 'tula.market.yandex.ru';
                    break;
                case '36':
                    $url = 'ivanovo.market.yandex.ru';
                    break;
                case '163':
                    $url = 'vladimir.market.yandex.ru';
                    break;
                case '207':
                    $url = 'nn.market.yandex.ru';
                    break;
                case '65':
                    $url = 'obninsk.market.yandex.ru';
                    break;
            }
        }

        $currentImage = (new FullHrefDecorator($offer->getImages()
            ->first()
            ->getSrc()))->setHost($host)
            ->__toString();
        $detailPath = (new FullHrefDecorator(\sprintf(
            '%s%sutm_source=%s&utm_term=%s&utm_medium=cpc&utm_campaign=main',
            $offer->getDetailPageUrl(),
            (\strpos($offer->getDetailPageUrl(), '?') > 0 ? '&' : '?'),
            $url,
            $offer->getXmlId()
        )))->setHost($host)
            ->__toString();

        $deliveryInfo = $this->getOfferDeliveryInfo($offer, $stockID);

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        /** @noinspection PassingByReferenceCorrectnessInspection */
        $yandexOffer =
            (new YandexOffer())
                ->setId($offer->getXmlId())
                ->setName(\sprintf(
                    '%s %s',
                    $offer->getProduct()
                        ->getBrandName(),
                    $offer->getName()
                ))
                ->setCategoryId($offer->getProduct()
                    ->getIblockSectionId())
                ->setDelivery(!$offer->getProduct()
                    ->isDeliveryForbidden())
                ->setPickup(true)
                ->setStore($offer->getDeliverableQuantity() > 0)
                ->setDescription(\substr(\strip_tags($offer->getProduct()
                    ->getDetailText()
                    ->getText()), 0, 2990))
                ->setManufacturerWarranty(true)
                ->setAvailable($offer->isAvailable())
                ->setCurrencyId('RUB')
                ->setPrice($offer->getPrice())
                ->setPicture($currentImage)
                ->setUrl($detailPath)
                ->setCpa(0)
                ->setVendor($offer->getProduct()
                    ->getBrandName())
                ->setDeliveryOptions($deliveryInfo)
                ->setVendorCode(\array_shift($offer->getBarcodes()) ?: '');

        $country = $offer
            ->getProduct()
            ->getCountry();
        if ($country) {
            $yandexOffer->setCountryOfOrigin($country->getName());
        }

        $params = $this->getOfferParam($offer);
        $yandexOffer->setParam($params);

        $collection->add($yandexOffer);
    }

    /**
     * Проверяем по стоп-словам, ТПЗ.
     *
     * @param Offer $offer
     *
     * @return bool
     *
     * @throws RuntimeException
     */
    protected function isOfferExcluded(Offer $offer): bool
    {
        $badWordsTemplate = '~новинка|подарка|хит|скидка|бесплатно|спеццена|специальная цена|новинка|заказ|аналог|акция|распродажа|новый|подарок|new|sale~iu';

        if (!$offer->getXmlId()) {
            return true;
        }

        if (
            preg_match($badWordsTemplate, $offer->getName()) > 0
            || preg_match(
                $badWordsTemplate,
                $offer->getProduct()
                    ->getDetailText()
                    ->getText()
            ) > 0
        ) {
            $this->log()
                ->info(
                    \sprintf(
                        'Offer %s was been excluded by stop word',
                        $offer->getXmlId()
                    )
                );

            return true;
        }

        if ((int)$offer->getPrice() === 0) {
            return true;
        }

        if ($offer->getAllStocks()->filterByStore($this->getRcStock())->getTotalAmount() < self::MINIMAL_AVAILABLE_IN_RC) {
            return true;
        }

        return false;
    }

    /**
     * @param Feed $feed
     * @param Configuration $configuration
     *
     * @return array
     *
     * @throws IblockNotFoundException
     */
    public function buildOfferFilter(Feed $feed, Configuration $configuration): array
    {
        $sectionIds = \array_reduce(
            $feed->getShop()
                ->getCategories()
                ->toArray(),
            function ($carry, YandexCategory $item) {
                return \array_merge($carry, [$item->getId()]);
            },
            []
        );

        $idList = [];

        try {
            $idList = \array_reduce(ElementTable::query()
                //->setCacheTtl(3600)
                ->setSelect(['ID'])
                ->setFilter([
                    'IBLOCK_ID' => IblockUtils::getIblockId(
                        IblockType::CATALOG,
                        IblockCode::PRODUCTS
                    ),
                    'IBLOCK_SECTION_ID' => $sectionIds,
                    'ACTIVE' => 'Y'
                ])
                ->exec()
                ->fetchAll() ?: [], function ($carry, $on) {
                $carry[] = $on['ID'];

                return $carry;
            }, []);
        } catch (Exception $e) {
        }

        $idList = $idList ?: [-1];

        return [
            '=PROPERTY_CML2_LINK' => $idList,
            '<XML_ID' => 2000000,
            'ACTIVE' => 'Y'
        ];
    }

    /**
     * @param Feed $feed
     * @param Configuration $configuration
     *
     * @return YandexFeedService
     */
    protected function processCategories(Feed $feed, Configuration $configuration): YandexFeedService
    {
        $categories = new ArrayCollection();

        /**
         * @var CategoryCollection $parentCategories
         */
        $parentCategories = (new CategoryQuery())
            ->withFilter([
                'ID' => $configuration->getSectionIds(),
                'GLOBAL_ACTIVE' => 'Y'
            ])
            ->withOrder(['LEFT_MARGIN' => 'ASC'])
            ->exec();

        /**
         * @var Category $parentCategory
         */
        foreach ($parentCategories as $parentCategory) {
            if ($categories->get($parentCategory->getId())) {
                continue;
            }

            $this->addCategory($parentCategory, $categories);

            if ($parentCategory->getRightMargin() - $parentCategory->getLeftMargin() < 3) {
                continue;
            }

            $childCategories = (new CategoryQuery())
                ->withFilter([
                    '>LEFT_MARGIN' => $parentCategory->getLeftMargin(),
                    '<RIGHT_MARGIN' => $parentCategory->getRightMargin(),
                    'GLOBAL_ACTIVE' => 'Y'
                ])
                ->withOrder(['LEFT_MARGIN' => 'ASC'])
                ->exec();

            foreach ($childCategories as $category) {
                $this->addCategory($category, $categories);
            }
        }

        $feed->getShop()
            ->setCategories($categories);

        return $this;
    }

    /**
     * @param Category $category
     * @param ArrayCollection $categoryCollection
     */
    protected function addCategory(Category $category, ArrayCollection $categoryCollection): void
    {
        $categoryCollection->set(
            $category->getId(),
            (new YandexCategory())
                ->setId($category->getId())
                ->setParentId($category->getIblockSectionId() ?: null)
                ->setName($category->getName())
        );
    }

    /**
     * @todo from db + profile
     *
     * @param Feed $feed
     * @param Configuration $configuration
     *
     * @param $stockID
     * @return $this
     */
    protected function processDeliveryOptions(Feed $feed, Configuration $configuration, $stockID): YandexFeedService
    {
        /**
         * По умолчанию в Мск вполне себе доступна бесплатная доставка и товар есть в магазине
         */
        $feed->getShop()
            ->setDeliveryOptions($this->getDeliveryInfo($stockID));

        return $this;
    }

    /**
     * @param string $key
     *
     * @return Feed
     */
    public function loadFeed(string $key): Feed
    {
        return parent::loadFeed($key);
    }

    /**
     * @return string
     */
    private function getStorageKey(): string
    {
        return \sprintf(
            '%s/yandex_tmp_feed.xml',
            \sys_get_temp_dir()
        );
    }

    /**
     * @param null $stockID
     * @return ArrayCollection|DeliveryOption[]
     */
    private function getDeliveryInfo($stockID = null): ArrayCollection
    {
        global $APPLICATION;

        $deliveryCollection = new ArrayCollection();
        if ($stockID == null) {
            $deliveryInfo = $APPLICATION->IncludeComponent('fourpaws:city.delivery.info',
                'empty',
                ['CACHE_TIME' => 3601 * 24],
                false,
                ['HIDE_ICONS' => 'Y'])['DELIVERIES'];
        } else {
            switch ($stockID) {
                case '47':
                    $locationCode = '0000263227';
                    break;
                case '151':
                    $locationCode = '0000293598';
                    break;
                case '168':
                    $locationCode = '0000250453';
                    break;
                case '36':
                    $locationCode = '0000121319';
                    break;
                case '163':
                    $locationCode = '0000312126';
                    break;
                case '207':
                    $locationCode = '0000600317';
                    break;
                case '65':
                    $locationCode = '0000148783';
                    break;
                default:
                    $locationCode = '0000073738';
            }
            $deliveryInfo = $APPLICATION->IncludeComponent('fourpaws:city.delivery.info',
                'empty',
                ['CACHE_TIME' => 3601 * 24, 'LOCATION_CODE' => $locationCode],
                false,
                ['HIDE_ICONS' => 'Y'])['DELIVERIES'];
        }

        foreach ($deliveryInfo as $delivery) {
            if ((int)$delivery['PRICE']) {
                $deliveryCollection->add(
                    (new DeliveryOption())
                        ->setCost((int)$delivery['PRICE'])
                        ->setDays((string)$delivery['PRICE'] ? (int)$delivery['PERIOD_FROM'] : 0)
                        ->setDaysBefore(13)
                        ->setFreeFrom((int)$delivery['FREE_FROM'])
                );
            }
        }

        $this->deliveryInfo = $deliveryCollection;

        return $this->deliveryInfo;
    }

    /**
     * @param Offer $offer
     * @return ArrayCollection
     */
    private function getOfferParam(Offer $offer): ArrayCollection
    {
        $params = new ArrayCollection();

        $curName = mb_strtolower($offer->getName());
        $curSectionName = mb_strtolower($offer->getProduct()->getSectionName());

        if (mb_strpos($curName, 'корм') !== false || mb_strpos($curSectionName, 'корм') !== false) {
            $curWeigth = WordHelper::showWeightNumber($offer->getCatalogProduct()->getWeight(), true);
            if ($curWeigth != null && $curWeigth != '' && floatval($curWeigth) > 0) {
                $paramWeigth = (new Param())
                    ->setName('Вес упаковки')
                    ->setUnit('кг')
                    ->setValue($curWeigth);

                $params->set('weigth', $paramWeigth);
            }
        } elseif (mb_strpos($curName, 'наполнитель') !== false || mb_strpos($curSectionName, 'наполнитель') !== false) {
            $curWeigth = WordHelper::showWeightNumber($offer->getCatalogProduct()->getWeight(), true);
            if ($curWeigth != null && $curWeigth != '' && floatval($curWeigth) > 0) {
                $paramWeigth = (new Param())
                    ->setName('Вес')
                    ->setUnit('кг')
                    ->setValue($curWeigth);

                $params->set('weigth', $paramWeigth);
            }

            $curVolume = $offer->getVolume();
            if ($curVolume != null && $curVolume != '' && floatval($curVolume) > 0) {
                $paramVolume = (new Param())
                    ->setName('Объем')
                    ->setUnit('л')
                    ->setValue($curVolume);

                $params->set('volume', $paramVolume);
            }
        }

        return $params;
    }

    /**
     * @param Offer $offer
     *
     * @param null $stockID
     * @return ArrayCollection
     * @throws ApplicationCreateException
     * @throws BitrixArgumentException
     * @throws DeliveryNotFoundException
     * @throws LoaderException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    private function getOfferDeliveryInfo(Offer $offer, $stockID = null): ArrayCollection
    {
        if ($offer->getProduct()
            ->isDeliveryForbidden()) {
            return new ArrayCollection();
        }


        $deliveryInfo = clone $this->getDeliveryInfo($stockID);

        foreach ($deliveryInfo as $option) {
            if ($offer->getDeliverableQuantity() < 1) {
                $option->setDays('1');
                $option->setDaysBefore(13);
            }

            if ($offer->getPrice() > $option->getFreeFrom()) {
                $option->setCost(0);
            }
        }

        return clone $deliveryInfo;
    }

    /**
     * @return Store
     */
    private function getRcStock(): Store
    {
        if (null === $this->rcStock) {
            $this->rcStock = $this->storeService->getStoreByXmlId('DC01');
        }

        return $this->rcStock;
    }


    /**
     * @param Feed $feed
     * @param ConfigurationInterface $configuration
     * @param string|null $stockID
     * @throws IblockNotFoundException
     */
    private function processPromos(Feed $feed, ConfigurationInterface $configuration, string $stockID = null)
    {
        $promos = $feed->getShop()->getPromos();
        $host = $configuration->getServerName();

        $arOrder = [
            'ID' => 'ASC'
        ];

        $time = ConvertTimeStamp(time(), 'FULL');

        $arFilter = [
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES),
            'ACTIVE' => 'Y',
            '<=DATE_ACTIVE_FROM' => $time,
            '>=DATE_ACTIVE_TO' => $time,
            '!PROPERTY_PRODUCTS' => false
        ];

        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'DATE_ACTIVE_FROM',
            'DATE_ACTIVE_TO',
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
            'DETAIL_PAGE_URL'
        ];

        $dbShare = \CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);

        while ($cibeShare = $dbShare->GetNextElement()) {
            $share = $cibeShare->GetFields();
            $share['PROPERTIES'] = $cibeShare->GetProperties();
            $matches = [];
            if (
                $share['DATE_ACTIVE_FROM'] != '' &&
                $share['DATE_ACTIVE_TO'] != '' &&
                (
                    $share['PROPERTIES']['SHARE_TYPE']['VALUE'] == '' ||
                    $share['PROPERTIES']['SHARE_TYPE']['VALUE'] == 'aktsiya-v-im-roznitse' ||
                    $share['PROPERTIES']['SHARE_TYPE']['VALUE'] == 'aktsiya-v-roznitse'
                ) &&
                preg_match('/[\s]?[0-9]{1,2}[+]{1}[0-9]{1,2}[\s]?/', $share['NAME'], $matches) !== false
            ) {
                if (strpos($share['DATE_ACTIVE_FROM'], ' ') === false) {
                    $share['DATE_ACTIVE_FROM'] .= ' 00:00:00';
                }
                if (strpos($share['DATE_ACTIVE_TO'], ' ') === false) {
                    $share['DATE_ACTIVE_TO'] .= ' 23:59:59';
                }
                if(count($matches) == 0){
                    continue;
                }
                $arType = explode('+', str_replace(' ', '', $matches[0]));
                $requiredQuantity = (int)$arType[0];
                $freeQuantity = (int)$arType[1];

                $offers = array_unique($share['PROPERTIES']['PRODUCTS']['VALUE']);
                $offers = array_flip($offers);

                $filter = [
                    'XML_ID' => array_keys($offers)
                ];

                if (!empty($stockID)) {
                    $filter['>CATALOG_STORE_AMOUNT_' . $stockID] = '1';
                } else {
                    $filter['>CATALOG_STORE_AMOUNT_' . $this->getRcStock()->getId()] = '1';
                }

                $offerCollection = (new OfferQuery())
                    ->withFilter($filter)
                    ->exec();

                /** @var Offer $offer */
                foreach ($offerCollection as $offer) {
                    $offers[$offer->getXmlId()] = true;
                }

                foreach ($offers as $offerId => $offer) {
                    if ($offer !== true) {
                        unset($offers[$offerId]);
                    }
                }

                $offers = array_keys($offers);
                $productCollection = new ArrayCollection();
                foreach ($offers as $offer) {
                    $product = new Product();
                    $product->setOfferId($offer);
                    $productCollection->add($product);
                }

                if ($productCollection->count() > 0) {
                    $purchase = new Purchase();
                    $purchase
                        ->setRequiredQuantity($requiredQuantity)
                        ->setFreeQuantity($freeQuantity)
                        ->setProduct($productCollection);
                    $descr = ($share['PREVIEW_TEXT']) ? $share['PREVIEW_TEXT'] : $share['DETAIL_TEXT'];
                    $descr = str_replace("\r\n", '', html_entity_decode(\HTMLToTxt($descr)));
                    $promo = new Promo();
                    $promo
                        ->setId($share['ID'])
                        ->setType($requiredQuantity . ' plus ' . $freeQuantity)
                        ->setUrl((new FullHrefDecorator($share['DETAIL_PAGE_URL']))->setHost($host)->__toString())
                        ->setStartDate(\DateTime::createFromFormat('d.m.Y H:i:s', $share['DATE_ACTIVE_FROM'])->format('Y-m-d H:i:s'))
                        ->setEndDate(\DateTime::createFromFormat('d.m.Y H:i:s', $share['DATE_ACTIVE_TO'])->format('Y-m-d H:i:s'))
                        ->setDescription($descr)
                        ->setPurchase($purchase);
                    $promos->add($promo);
                }
            }
        }

        $feed->getShop()->setPromos($promos);
    }
}
