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
use Bitrix\Main\SystemException;
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
use FourPaws\CatalogBundle\Dto\RetailRocket\Offer as RetailRocketOffer;
use FourPaws\CatalogBundle\Dto\RetailRocket\Parameter;
use FourPaws\CatalogBundle\Dto\RetailRocket\Category as RrCategory;
use FourPaws\CatalogBundle\Dto\RetailRocket\Feed;
use FourPaws\CatalogBundle\Dto\RetailRocket\Shop;
use FourPaws\CatalogBundle\Exception\ArgumentException;
use FourPaws\CatalogBundle\Exception\OffersIsOver;
use FourPaws\CatalogBundle\Helper\YmlParameterHelper;
use FourPaws\CatalogBundle\Translate\Configuration;
use FourPaws\CatalogBundle\Translate\ConfigurationInterface;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\DeliveryBundle\Exception\NotFoundException as DeliveryNotFoundException;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\StoreBundle\Exception\NotFoundException;
use InvalidArgumentException;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class RetailRocketFeedService
 *
 * @package FourPaws\CatalogBundle\Service
 */
class RetailRocketFeedService extends FeedService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const MAX_OFFER_PARAMETERS = 40;

    /**
     * RetailRocketFeedService constructor.
     *
     * @param SerializerInterface $serializer
     * @param Filesystem $filesystem
     */
    public function __construct(SerializerInterface $serializer, Filesystem $filesystem)
    {
        parent::__construct($serializer, $filesystem, Feed::class);
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
        $this->tmpFileName = 'retail_rocket_tmp_feed.xml';

        /**
         * @var Configuration $configuration
         */

        if ($step === 0) {
            $this->clearFeed($this->getStorageKey());

            $feed = new Feed();
            $this->processFeed($feed, $configuration)
                ->processCategories($feed, $configuration, false);

            $this->saveFeed($this->getStorageKey(), $feed);
        } else {
            $feed = $this->loadFeed($this->getStorageKey());

            try {
                $this->processOffers($feed, $configuration);
            } catch (OffersIsOver $isOver) {
                $feed = $this->loadFeed($this->getStorageKey());
                $feed->getShop()
                    ->setOffset(null);
                $this->processCategories($feed, $configuration, true);
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
     * @return RetailRocketFeedService
     */
    protected function processFeed(Feed $feed, Configuration $configuration): RetailRocketFeedService
    {
        $feed
            ->setDate(new DateTime())
            ->setShop(new Shop());

        return $this;
    }

    /**
     * @param Feed $feed
     * @param Configuration $configuration
     *
     * @return RetailRocketFeedService
     *
     * @throws RuntimeException
     * @throws IblockNotFoundException
     * @throws IOException
     * @throws OffersIsOver
     * @throws ArgumentException
     */
    protected function processOffers(Feed $feed, Configuration $configuration): RetailRocketFeedService
    {
        $limit = 500;
        $offers = $feed->getShop()
            ->getOffers();

        $offset = $feed->getShop()
            ->getOffset();
        $offset = $offset ?? 0;

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
     * @param Offer $offer
     * @param ArrayCollection $collection
     * @param string $host
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
     * @throws SystemException
     * @throws \LogicException
     */
    public function addOffer(Offer $offer, ArrayCollection $collection, string $host): void
    {
        $currentImage = (new FullHrefDecorator($offer->getResizeImages(240, 240)
            ->first()
            ->getSrc()))->setHost($host)->__toString();
        $detailPath = (new FullHrefDecorator($offer->getDetailPageUrl()))->setHost($host)->__toString();

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        /** @noinspection PassingByReferenceCorrectnessInspection */
        $rrOffer =
            (new RetailRocketOffer())
                ->setId($offer->getXmlId())
                ->setName(\sprintf(
                    '%s %s',
                    $offer->getProduct()
                        ->getBrandName(),
                    $offer->getName()
                ))
                ->setCategoryId($offer->getProduct()
                    ->getIblockSectionId())
                ->setDescription(\substr(\strip_tags($offer->getProduct()
                    ->getDetailText()
                    ->getText()), 0, 2990))
                ->setAvailable($offer->isAvailable())
                ->setGroupId($offer->getProduct()->getId())
                ->setPrice($offer->getPrice())
                ->setPicture($currentImage)
                ->setUrl($detailPath)
                ->setVendor($offer->getProduct()->getBrandName());

        $helper = new YmlParameterHelper(
            Parameter::class,
            static::MAX_OFFER_PARAMETERS
        );
        $rrOffer->setParameters($helper->getOfferParameters($offer));

        $collection->add($rrOffer);
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
            function ($carry, RrCategory $item) {
                return \array_merge($carry, [$item->getId()]);
            },
            []
        );

        $dbItems = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => IblockUtils::getIblockId(
                    IblockType::CATALOG,
                    IblockCode::PRODUCTS
                ),
                'SECTION_ID' => $sectionIds,
                'INCLUDE_SUBSECTIONS' => 'Y',
                'ACTIVE' => 'Y'
            ],
            false,
            false,
            [
                'ID',
                'IBLOCK_ID'
            ]
        );
        $idList = [];
        while ($arItem = $dbItems->Fetch()) {
            $idList[] = $arItem['ID'];
        }

        $idList = $idList ?: [-1];

        return [
            '=PROPERTY_CML2_LINK' => $idList,
            '<XML_ID' => 2000000,
            'ACTIVE' => 'Y',
        ];
    }

    /**
     * @param Category $category
     * @param ArrayCollection $categoryCollection
     */
    protected function addCategory(Category $category, ArrayCollection $categoryCollection): void
    {
        $categoryCollection->set(
            $category->getId(),
            (new RrCategory())
                ->setId($category->getId())
                ->setParentId($category->getIblockSectionId() ?: null)
                ->setName(
                    \implode(' - ',
                        \array_reverse($category->getFullPathCollection()
                            ->map(function (Category $category) {
                                return \preg_replace('~\'|"~', '', $category->getName());
                            })
                            ->toArray()
                        )
                    )
                )
        );
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
}
