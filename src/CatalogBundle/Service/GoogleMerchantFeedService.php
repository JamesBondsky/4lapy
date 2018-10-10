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
use FourPaws\CatalogBundle\Dto\GoogleMerchant\Channel;
use FourPaws\CatalogBundle\Dto\GoogleMerchant\Feed;
use FourPaws\CatalogBundle\Dto\GoogleMerchant\Item;
use FourPaws\CatalogBundle\Exception\ArgumentException;
use FourPaws\CatalogBundle\Exception\OffersIsOver;
use FourPaws\CatalogBundle\Translate\Configuration;
use FourPaws\CatalogBundle\Translate\ConfigurationInterface;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
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
 * Class GoogleMerchantFeedService
 *
 * @package FourPaws\CatalogBundle\Service
 */
class GoogleMerchantFeedService extends FeedService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;
    private $rcStock;
    /**
     * @var StoreService
     */
    private $storeService;

    /**
     * GoogleMerchantFeedService constructor.
     *
     * @param SerializerInterface $serializer
     * @param Filesystem          $filesystem
     */
    public function __construct(SerializerInterface $serializer, Filesystem $filesystem, StoreService $storeService)
    {
        parent::__construct($serializer, $filesystem, Feed::class);

        $this->storeService = $storeService;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param int                    $step
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
    public function process(ConfigurationInterface $configuration, int $step): bool
    {
        /**
         * @var Configuration $configuration
         */

        if ($step === 0) {
            $this->clearFeed($this->getStorageKey());

            $feed = new Feed();
            $this->processFeed($feed);

            $this->saveFeed($this->getStorageKey(), $feed);
        } else {
            $feed = $this->loadFeed($this->getStorageKey());

            try {
                $this->processOffers($feed, $configuration);
            } catch (OffersIsOver $isOver) {
                $feed = $this->loadFeed($this->getStorageKey());
                $feed->getChannel()->setOffset(null);

                $this->publicFeed($feed, Application::getAbsolutePath($configuration->getExportFile()));
                $this->clearFeed($this->getStorageKey());

                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    private function getStorageKey(): string
    {
        return \sprintf(
            '%s/google_merchant_tmp_feed.xml',
            \sys_get_temp_dir()
        );
    }

    /**
     * @param Feed $feed
     *
     * @return GoogleMerchantFeedService
     */
    protected function processFeed(Feed $feed): GoogleMerchantFeedService
    {
        $feed->setChannel(new Channel());

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
     * @param Feed          $feed
     * @param Configuration $configuration
     *
     * @return GoogleMerchantFeedService
     *
     * @throws RuntimeException
     * @throws IblockNotFoundException
     * @throws IOException
     * @throws OffersIsOver
     * @throws ArgumentException
     */
    protected function processOffers(Feed $feed, Configuration $configuration): GoogleMerchantFeedService
    {
        $limit = 500;
        $offers = $feed->getChannel()->getItems();

        $offset = $feed->getChannel()->getOffset() ?? 0;

        $offerCollection = $this->getOffers($this->buildOfferFilter($feed, $configuration), $offset, $limit);

        $this->log()->info(
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
                $this->addOffer($offer, $offers, $configuration->getServerName());
            } catch (Exception $e) {
                /** Просто подавляем исключение */
            }
        }


        $feed->getChannel()
             ->setItems($offers)
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
     * @param int   $offset
     * @param int   $limit
     *
     * @return OfferCollection
     */
    protected function getOffers(array $filter, int $offset = 0, $limit = 500): OfferCollection
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return (new OfferQuery())->withFilter($filter)
                                 ->withNav([
                                     'nPageSize' => $limit,
                                     'iNumPage'  => $this->getPageNumber($offset, $limit),
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
     * @param Configuration $configuration
     *
     * @return array
     */
    protected function getCategoryIds(Configuration $configuration): array
    {
        $ids = [];
        /**
         * @var CategoryCollection $parentCategories
         */
        $parentCategories = (new CategoryQuery())
            ->withFilter([
                'ID'            => $configuration->getSectionIds(),
                'GLOBAL_ACTIVE' => 'Y'
            ])
            ->withSelect([
                'ID',
                'IBLOCK_SECTION_ID',
                'DEPTH_LEVEL',
                'LEFT_MARGIN',
                'RIGHT_MARGIN',
            ])
            ->withOrder(['LEFT_MARGIN' => 'ASC'])
            ->exec();

        /**
         * @var Category $parentCategory
         */
        foreach ($parentCategories as $parentCategory) {
            if ($ids[$parentCategory->getId()]) {
                continue;
            }

            $ids[$parentCategory->getId()] = $parentCategory;

            if ($parentCategory->getRightMargin() - $parentCategory->getLeftMargin() < 3) {
                continue;
            }

            $childCategories = (new CategoryQuery())
                ->withFilter([
                    '>LEFT_MARGIN'  => $parentCategory->getLeftMargin(),
                    '<RIGHT_MARGIN' => $parentCategory->getRightMargin(),
                    'GLOBAL_ACTIVE' => 'Y'
                ])
                ->withSelect([
                    'ID',
                    'IBLOCK_SECTION_ID',
                    'DEPTH_LEVEL',
                    'LEFT_MARGIN',
                    'RIGHT_MARGIN',
                ])
                ->withOrder(['LEFT_MARGIN' => 'ASC'])
                ->exec();

            foreach ($childCategories as $category) {
                $ids[$category->getId()] = $category;
            }
        }

        return $ids;
    }

    /**
     * @param Feed          $feed
     * @param Configuration $configuration
     *
     * @return array
     *
     * @throws IblockNotFoundException
     */
    public function buildOfferFilter(Feed $feed, Configuration $configuration): array
    {
        $sectionIds = \array_column($this->getCategoryIds($configuration), 'ID');
        dump($sectionIds);
        die;

        $idList = [];

        try {
            $idList = \array_reduce(
                ElementTable::query()
                            ->setSelect(['ID'])
                            ->setFilter([
                                'IBLOCK_ID'         => IblockUtils::getIblockId(
                                    IblockType::CATALOG,
                                    IblockCode::PRODUCTS
                                ),
                                'IBLOCK_SECTION_ID' => $sectionIds,
                                'ACTIVE'            => 'Y'
                            ])
                            ->exec()
                            ->fetchAll()
                    ?: [],
                function ($carry, $on) {
                    $carry[] = $on['ID'];

                    return $carry;
                }, []);
        } catch (Exception $e) {
        }

        $idList = $idList ?: [-1];

        return [
            '=PROPERTY_CML2_LINK' => $idList,
            '<XML_ID'             => 2000000,
            'ACTIVE'              => 'Y'
        ];
    }

    /**
     * @param Offer           $offer
     * @param ArrayCollection $collection
     * @param string          $host
     *
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws DeliveryNotFoundException
     * @throws ObjectNotFoundException
     * @throws NotSupportedException
     * @throws LoaderException
     * @throws BitrixArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws RuntimeException
     * @throws ApplicationCreateException
     */
    public function addOffer(Offer $offer, ArrayCollection $collection, string $host): void
    {
        if (
            !$offer->getXmlId()
            || (int)$offer->getPrice() === 0
            || $offer->getAllStocks()->filterByStore($this->getRcStock())->getTotalAmount() < 1
        ) {
            return;
        }

        $currentImage = (new FullHrefDecorator($offer->getImages()
                                                     ->first()
                                                     ->getSrc()))->setHost($host)->__toString();
        $detailPath = (new FullHrefDecorator($offer->getDetailPageUrl()))->setHost($host)->__toString();

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        /** @noinspection PassingByReferenceCorrectnessInspection */
        $item = (new Item())
            ->setId($offer->getXmlId())
            ->setName(\sprintf(
                '%s %s',
                $offer->getProduct()
                      ->getBrandName(),
                $offer->getName()
            ))
            ->setLink($detailPath)
            ->setGroupId($offer->getProduct()->getGoogleCategory())
            ->setDescription(\substr(\strip_tags($offer->getProduct()
                                                       ->getDetailText()
                                                       ->getText()), 0, 4990))
            ->setPicture($currentImage)
            ->setVendor($offer->getProduct()->getBrandName())
            ->setGtin($offer->getBarcodes()[0] ?? '');

        if ($offer->getOldPrice()) {
            $item->setPrice(
                \sprintf(
                    '%d RUR',
                    $offer->getCatalogOldPrice()
                ))
                 ->setSalePrice(\sprintf(
                     '%d RUR',
                     $offer->getCatalogPrice()
                 ));
        } else {
            $item->setPrice(
                \sprintf(
                    '%d RUR',
                    $offer->getCatalogPrice()
                ));
        }

        $collection->add($item);
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
}
