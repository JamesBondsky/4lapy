<?php

namespace FourPaws\CatalogBundle\Service;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\CatalogBundle\Dto\Yandex\Currency;
use FourPaws\CatalogBundle\Dto\Yandex\Feed;
use FourPaws\CatalogBundle\Dto\Yandex\Shop;
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
        } else {
            $feed = $this->loadFeed();
            try {
                $this->processOffers($feed, $configuration);
            } catch (OffersIsOver $isOver) {
                $this->publicFeed($this->loadFeed(), $configuration->getExportFile());
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
     */
    protected function processOffers(Feed $feed, Configuration $configuration): YandexFeedService
    {


        return $this;
    }

    /**
     * @param Feed          $feed
     * @param Configuration $configuration
     *
     * @return YandexFeedService
     */
    protected function processCategories(Feed $feed, Configuration $configuration): YandexFeedService
    {
        $parentCategories = (new CategoryQuery())->withFilter(['@ID' => $configuration->getSectionIds()])->exec();

        return $this;
    }

    /**
     * @param Feed          $feed
     * @param Configuration $configuration
     *
     * @return $this
     */
    protected function processDeliveryOptions(Feed $feed, Configuration $configuration): YandexFeedService
    {


        return $this;
    }

    /**
     * @return Feed
     */
    public function loadFeed(): Feed
    {
        return parent::loadFeed();
    }
}
