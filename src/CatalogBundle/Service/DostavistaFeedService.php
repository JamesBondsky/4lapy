<?php

namespace FourPaws\CatalogBundle\Service;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException as BitrixArgumentException;
use CCatalogStoreProduct;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FourPaws\App\Application;
use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\CatalogBundle\Dto\Dostavista\Category as DostavistaCategory;
use FourPaws\CatalogBundle\Dto\Dostavista\Currency;
use FourPaws\CatalogBundle\Dto\Dostavista\Feed;
use FourPaws\CatalogBundle\Dto\Dostavista\Merchant;
use FourPaws\CatalogBundle\Dto\Dostavista\Offer;
use FourPaws\CatalogBundle\Dto\Dostavista\Residue;
use FourPaws\CatalogBundle\Dto\Dostavista\Weight;
use FourPaws\CatalogBundle\Dto\Dostavista\Shop;
use FourPaws\CatalogBundle\Dto\Dostavista\Worktime;
use FourPaws\CatalogBundle\Translate\Configuration;
use FourPaws\CatalogBundle\Translate\ConfigurationInterface;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Filesystem\Filesystem;
use FourPaws\Catalog\Model\Offer as ModelOffer;
use FourPaws\Helpers\WordHelper;

/**
 * Class DostavistaFeedService
 *
 * @package FourPaws\CatalogBundle\Service
 */
class DostavistaFeedService extends FeedService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const WEEK_DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public const PATTERN_TIME = '~^(\d{1,2}:\d{1,2})\D+(\d{1,2}:\d{1,2})$~';

    /**
     * @var StoreService $storeService
     */
    private $storeService;

    /**
     * @var array $products
     */
    private $products = [];

    /**
     * @var array $offers
     */
    private $offers = [];

    /**
     * @var array $stocks
     */
    private $stocks = [];

    /**
     * DostavistaFeedService constructor.
     *
     * @param SerializerInterface $serializer
     * @param Filesystem $filesystem
     * @param StoreService $storeService
     */
    public function __construct(SerializerInterface $serializer, Filesystem $filesystem, StoreService $storeService)
    {
        parent::__construct($serializer, $filesystem, Feed::class);

        $this->storeService = $storeService;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param int $step
     *
     * If need to continue, return true. Else - false.
     *
     * @param string|null $stockID
     * @return boolean
     *
     * @throws Exception
     */
    public function process(ConfigurationInterface $configuration, int $step, string $stockID = null): bool
    {
        /**
         * @var Configuration $configuration
         */

        $feed = new Feed();
        $this
            ->processFeed($feed, $configuration)
            ->processCurrencies($feed, $configuration)
            ->processCategories($feed, $configuration)
            ->processMerchants($feed)
            ->processProducts($configuration)
            ->processOffers($feed, $configuration);

        $this->removeExtraData($feed);

        $this->publicFeed($feed, Application::getAbsolutePath($configuration->getExportFile()));

        return true;
    }


    /**
     * @param Feed $feed
     * @param Configuration $configuration
     * @return DostavistaFeedService
     * @throws Exception
     */
    protected function processFeed(Feed $feed, Configuration $configuration): DostavistaFeedService
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
     * @return DostavistaFeedService
     */
    protected function processCurrencies(Feed $feed, Configuration $configuration): DostavistaFeedService
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
     *
     * @return DostavistaFeedService
     */
    protected function processCategories(Feed $feed, Configuration $configuration): DostavistaFeedService
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

        $feed->getShop()->setCategories($categories);

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
            (new DostavistaCategory())
                ->setId($category->getId())
                ->setParentId($category->getIblockSectionId() ?: null)
                ->setName($category->getName())
        );
    }

    /**
     * @param Feed $feed
     * @return DostavistaFeedService
     * @throws BitrixArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function processMerchants(Feed $feed): DostavistaFeedService
    {
        $merchants = new ArrayCollection();
        $storeCollection = $this->storeService->getStores(StoreService::TYPE_ALL, ['UF_AVAILABLE_EXPRESS' => true], ['ID' => 'ASC']);
        /** @var Store $store */
        foreach ($storeCollection as $store) {
            $workTimeError = false;
            $merchant = new Merchant();
            $workTimes = new ArrayCollection();
            foreach (self::WEEK_DAYS as $day) {
                $workTime = new Worktime();
                preg_match(static::PATTERN_TIME, (string)$store->getSchedule(), $matches);
                if (empty($matches)) {
                    $workTimeError = true;
                    continue;
                }
                $workTime->setStartTime($matches[1]);
                $workTime->setEndTime($matches[2]);
                $workTime->setDay($day);
                $workTimes->add($workTime);
            }
            if ($workTimeError) {
                continue;
            }

            $this->stocks[$store->getId()] = false;

            $merchant
                ->setId($store->getId())
                ->setAddress('г. Москва, ' . str_replace(', Москва', '', $store->getAddress()))
                ->setWorktimes($workTimes);
            $merchants->add($merchant);
        }

        $feed->getShop()->setMerchants($merchants);
        return $this;
    }

    /**
     * @param Configuration $configuration
     * @return DostavistaFeedService
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    protected function processProducts(Configuration $configuration): DostavistaFeedService
    {
        $arFilter = [
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
            'SECTION_ID' => $configuration->getSectionIds(),
            'INCLUDE_SUBSECTIONS' => 'Y',
            'ACTIVE' => 'Y'
        ];

        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'XML_ID',
            'IBLOCK_SECTION_ID',
            'DETAIL_TEXT',
            'PROPERTY_BRAND.NAME'
        ];

        $dbItems = \CIBlockElement::GetList(['XML_ID' => 'ASC'], $arFilter, false, false, $arSelect);
        $productsCnt = $dbItems->SelectedRowsCount();
        $i = 1;
        $this->printDumpString('processProducts');
        while ($arProduct = $dbItems->Fetch()) {
            $descr = preg_replace(
                [
                    '/<table(.*)<\/table>/', //таблица
                    '/<a(.*)<\/a>/', //ссылки
                    '/<img(.*)>/' //изображения
                ],
                ['', '', ''],
                $arProduct['DETAIL_TEXT']
            );
            $descr = str_replace("\r\n", '', html_entity_decode(\HTMLToTxt($descr))); //удаляем остальные теги
            $this->products[$arProduct['ID']] = [
                'DESCRIPTION' => $descr,
                'IBLOCK_SECTION_ID' => $arProduct['IBLOCK_SECTION_ID'],
                'BRAND' => $arProduct['PROPERTY_BRAND_NAME']
            ];
            if ($i % 500 == 0) {
                dump('Products added to xml:' . $i . '/' . $productsCnt);
            }
            $i++;
        }

        $this->printDumpString('processProducts done');

        return $this;
    }

    /**
     * @param Feed $feed
     * @param Configuration $configuration
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    protected function processOffers(Feed $feed, Configuration $configuration)
    {
        $offerCollection = new ArrayCollection();
        $arFilter = [
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
            'ACTIVE' => 'Y',
            'PROPERTY_CML2_LINK' => array_keys($this->products),
            '!PROPERTY_IMG' => false
        ];

        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'CATALOG_GROUP_2',
            'XML_ID',
            'DETAIL_PAGE_URL',
            'CATALOG_WEIGHT'
        ];

        $files = [];
        $dbItems = \CIBlockElement::GetList(['XML_ID' => 'ASC'], $arFilter, false, ['nTopCount' => 100], $arSelect);
        $offerCnt = $dbItems->SelectedRowsCount();
        $i = 1;

        $stocks = $this->stocks;
        $this->stocks = [];

        $this->printDumpString('processOffers');
        while ($cibOffer = $dbItems->GetNextElement()) {
            $arOffer = $cibOffer->GetFields();
            $arOffer['PROPERTIES'] = $cibOffer->GetProperties();

            $price = floatval($arOffer['CATALOG_PRICE_2']);
            switch ($arOffer['PROPERTIES']['COND_FOR_ACTION']['VALUE']) {
                case ModelOffer::SIMPLE_SHARE_SALE_CODE:
                    if (
                        (floatval($arOffer['PROPERTIES']['PRICE_ACTION']['VALUE']) < $price)
                        && $arOffer['PROPERTIES']['PRICE_ACTION']['VALUE'] != ''
                        && $arOffer['PROPERTIES']['PRICE_ACTION']['VALUE'] != 0
                    ) {
                        $price = floatval($arOffer['PROPERTIES']['PRICE_ACTION']['VALUE']);
                    }
                    break;
                case ModelOffer::SIMPLE_SHARE_DISCOUNT_CODE:
                    if (
                        intval($arOffer['PROPERTIES']['COND_VALUE']['VALUE']) != '' &&
                        intval($arOffer['PROPERTIES']['COND_VALUE']['VALUE']) > 0

                    ) {
                        $price = ceil($price - $price / 100 * intval($arOffer['PROPERTIES']['COND_VALUE']['VALUE']));
                    }
                    break;
                default:
            }

            $detailPath = (new FullHrefDecorator($arOffer['DETAIL_PAGE_URL']))->setHost($configuration->getServerName())->__toString();
            $productID = $arOffer['PROPERTIES']['CML2_LINK']['VALUE'];
            $descr = $this->products[$productID]['DESCRIPTION'];
            $sectionID = $this->products[$productID]['IBLOCK_SECTION_ID'];
            $brand = $this->products[$productID]['BRAND'];

            $this->offers[$arOffer['ID']] = [
                'XML_ID' => $arOffer['XML_ID'],
                'URL' => $detailPath,
                'PRICE' => $price,
                'CURRENCY' => $arOffer['CATALOG_CURRENCY_2'],
                'SECTION_ID' => $sectionID,
                'NAME' => $brand . ' ' . $arOffer['NAME'],
                'DESCRIPTION' => $descr,
                'PICTURE' => '',
                'RESIDUES' => [],
                'WEIGHT' => WordHelper::showWeightNumber($arOffer['CATALOG_WEIGHT'], true)
            ];

            if (!empty($arOffer['PROPERTIES']['IMG']['VALUE'][0])) {
                $files[$arOffer['PROPERTIES']['IMG']['VALUE'][0]] = $arOffer['ID'];
            }

            $arFilter = [
                'PRODUCT_ID' => $arOffer['ID'],
                'STORE_ID' => array_keys($stocks),
                '>AMOUNT' => 0
            ];
            $arSelect = [
                'PRODUCT_ID',
                'STORE_ID',
                'AMOUNT'
            ];

            $rsStore = CCatalogStoreProduct::GetList([], $arFilter, false, false, $arSelect);
            while ($arStore = $rsStore->Fetch()) {
                if (isset($this->offers[$arStore['PRODUCT_ID']]['RESIDUES'])) {
                    $this->offers[$arStore['PRODUCT_ID']]['RESIDUES'][] = [
                        'MERCHANT_ID' => $arStore['STORE_ID'],
                        'AMOUNT' => $arStore['AMOUNT'],
                        'PRICE' => $this->offers[$arStore['PRODUCT_ID']]['PRICE']
                    ];
                    $this->stocks[$arStore['STORE_ID']] = $arStore['STORE_ID'];
                }
            }

            if ($i % 500 == 0) {
                dump('Offers added to xml:' . $i . '/' . $offerCnt);
            }
            $i++;
        }

        $this->printDumpString('processOffers done');

        $this->setFilesPaths($configuration, $files);

        $this->printDumpString('processClearEmptyImageOffers');
        foreach ($this->offers as $key => $offer) {
            if ($offer['PICTURE'] == '') {
                dump('Offer ' . $offer['XML_ID'] . ' without image clear');
                unset($this->offers[$key]);
            }
        }
        $this->printDumpString('processClearEmptyImageOffers done');

        $this->printDumpString('processOffersXmlModelCreate');
        foreach ($this->offers as $arOffer) {
            $offer = new Offer();
            $residues = new ArrayCollection();
            foreach ($arOffer['RESIDUES'] as $arResidue) {
                $residue = new Residue();
                $residue
                    ->setMerchantId($arResidue['MERCHANT_ID'])
                    ->setAmount($arResidue['AMOUNT'])
                    ->setPrice($arResidue['PRICE']);
                $residues->add($residue);
            }
            $weight = new Weight();
            $weight->setUnitId('kg')->setValue($arOffer['WEIGHT']);
            $offer->setId($arOffer['XML_ID'])
                ->setUrl($arOffer['URL'])
                ->setPrice($arOffer['PRICE'])
                ->setCurrencyId($arOffer['CURRENCY'])
                ->setCategoryId($arOffer['SECTION_ID'])
                ->setName($arOffer['NAME'])
                ->setDescription($arOffer['DESCRIPTION'])
                ->setPicture($arOffer['PICTURE'])
                ->setResidues($residues)
                ->setWeight($weight);
            $offerCollection->add($offer);
        }
        $this->printDumpString('processOffersXmlModelCreate done');

        $feed->getShop()->setOffers($offerCollection);
    }


    /**
     * @param Configuration $configuration
     * @param array $files
     * @return bool
     */
    private function setFilesPaths(Configuration $configuration, array $files): bool
    {
        $uploadDir = \COption::GetOptionString('main', 'upload_dir', 'upload');
        $dbFiles = \CFile::GetList([], ['@ID' => implode(',', array_keys($files))]);
        $this->printDumpString('processSetFilesPaths');

        while ($file = $dbFiles->Fetch()) {
            $path = '/' . $uploadDir . '/' . $file['SUBDIR'] . '/' . $file['FILE_NAME'];
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                unset($this->offers[$files[$file['ID']]]);
            }

            $path = (new FullHrefDecorator($path))->setHost($configuration->getServerName())->__toString();

            if (isset($this->offers[$files[$file['ID']]]['PICTURE'])) {
                $this->offers[$files[$file['ID']]]['PICTURE'] = $path;
            }
        }

        $this->printDumpString('processSetFilesPaths done');

        return true;
    }

    /**
     * @param Feed $feed
     */
    private function removeExtraData(Feed $feed)
    {
        $this->printDumpString('processRemoveExtraData');
        foreach ($feed->getShop()->getOffers() as $key => $offer) {
            if ($offer->getResidues()->isEmpty()) {
                dump('Offer ' . $offer->getId() . ' without stock result clear');
                $feed->getShop()->getOffers()->remove($key);
            }
        }

        foreach ($feed->getShop()->getMerchants() as $key => $merchant) {
            if (!in_array($merchant->getId(), $this->stocks)) {
                dump('Store ' . $merchant->getId() . ' without use offers clear');
                $feed->getShop()->getMerchants()->remove($key);
            }
        }
        $this->printDumpString('processRemoveExtraData done');
    }

    /**
     * @param string $title
     */
    protected function printDumpString(string $title): void
    {
        $part = '#####';
        $sharps = '';
        for ($i = 0; $i < strlen($title); $i++) {
            $sharps .= '#';
        }
        dump($part . $sharps . $part);
        dump($part . $title . $part);
        dump($part . $sharps . $part);
        dump('');
    }
}