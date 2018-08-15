<?php

namespace FourPaws\CatalogBundle\Service;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\CatalogBundle\Dto\Yandex\Category as YandexCategory;
use FourPaws\CatalogBundle\Dto\Yandex\Currency;
use FourPaws\CatalogBundle\Dto\Yandex\DeliveryOption;
use FourPaws\CatalogBundle\Dto\Yandex\Feed;
use FourPaws\CatalogBundle\Dto\Yandex\Shop;
use FourPaws\CatalogBundle\Exception\ArgumentException;
use FourPaws\CatalogBundle\Exception\OffersIsOver;
use FourPaws\CatalogBundle\Translate\Configuration;
use FourPaws\CatalogBundle\Translate\ConfigurationInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class YandexFeedService
 *
 * @package FourPaws\CatalogBundle\Service
 */
class YandexFeedService extends FeedService
{
    /**
     * YandexFeedService constructor.
     *
     * @param SerializerInterface $serializer
     * @param Filesystem          $filesystem
     */
    public function __construct(SerializerInterface $serializer, Filesystem $filesystem)
    {
        parent::__construct($serializer, $filesystem, Feed::class);
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param int                    $step
     *
     * If need to continue, return true. Else - false.
     *
     * @return boolean
     *
     * @throws ArgumentException
     * @throws IOException
     */
    public function process(ConfigurationInterface $configuration, int $step): bool
    {
        /**
         * @var Configuration $configuration
         */

        if ($step === 0) {
            $feed = new Feed();
            $this
                ->processFeed($feed, $configuration)
                ->processCurrencies($feed, $configuration)
                ->processDeliveryOptions($feed, $configuration)
                ->processCategories($feed, $configuration);

            $this->saveFeed($this->getStorageKey(), $feed);
        } else {
            $feed = $this->loadFeed($this->getStorageKey());

            try {
                $this->processOffers($feed, $configuration);
            } catch (OffersIsOver $isOver) {
                $this->publicFeed($this->loadFeed($this->getStorageKey()), Application::getAbsolutePath($configuration->getExportFile()));
                $this->clearFeed($this->getStorageKey());

                return false;
            }
        }

        return true;
    }

    /**
     * @param Feed          $feed
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
     * @param Feed          $feed
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
     * @param Feed          $feed
     * @param Configuration $configuration
     *
     * @return YandexFeedService
     *
     * @throws IOException
     * @throws OffersIsOver
     * @throws ArgumentException
     */
    protected function processOffers(Feed $feed, Configuration $configuration): YandexFeedService
    {
        $this->saveFeed($this->getStorageKey(), $feed);

        throw new OffersIsOver('All offers was been processed.');

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

    }

    /**
     * @param Offer           $offer
     * @param ArrayCollection $arrayCollection
     */
    public function addOffer(Offer $offer, ArrayCollection $arrayCollection): void
    {

    }

    /**
     * Проверяем по стоп-словам.
     *
     * @param Offer $offer
     *
     * @return bool
     */
    public function isOfferExcluded(Offer $offer): bool {


        return false;
    }

    /**
     * @param Feed          $feed
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
                'ID'            => $configuration->getSectionIds(),
                'GLOBAL_ACTIVE' => 'Y'
            ])
            ->withOrder(['LEFT_MARGIN' => 'ASC'])
            ->exec();

        /**
         * @var Category $parentCategory
         */
        foreach ($parentCategories as $parentCategory) {
            if ($parentCategory->getRightMargin() - $parentCategory->getLeftMargin() < 3) {
                continue;
            }

            if ($categories->get($parentCategory->getId())) {
                continue;
            }

            $childCategories = (new CategoryQuery())
                ->withFilter([
                    '>LEFT_MARGIN'  => $parentCategory->getLeftMargin(),
                    '<RIGHT_MARGIN' => $parentCategory->getRightMargin(),
                    'GLOBAL_ACTIVE' => 'Y'
                ])
                ->withOrder(['LEFT_MARGIN' => 'ASC'])
                ->exec();

            $this->addCategory($parentCategory, $categories);

            foreach ($childCategories as $category) {
                $this->addCategory($category, $categories);
            }
        }

        $feed->getShop()
            ->setCategories($categories);

        return $this;
    }

    /**
     * @param Category        $category
     * @param ArrayCollection $categoryCollection
     */
    protected function addCategory(Category $category, ArrayCollection $categoryCollection): void
    {
        $categoryCollection->set(
            $category->getId(),
            (new YandexCategory())
                ->setId($category->getId())
                ->setParentId($category->getIblockSectionId() ?: null)
                ->setName(
                    \implode(' | ',
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
     * @todo from db + profile
     *
     * @param Feed          $feed
     * @param Configuration $configuration
     *
     * @return $this
     */
    protected function processDeliveryOptions(Feed $feed, Configuration $configuration): YandexFeedService
    {
        $options = new ArrayCollection();
        $options->add((new DeliveryOption())
            ->setCost(0)
            ->setDays(1)
        );

        /**
         * По умолчанию в Мск вполне себе доступна бесплатная доставка и товар есть в магазине
         */
        $feed->getShop()
            ->setDeliveryOptions($options);

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
}
