<?php

namespace FourPaws\CatalogBundle\Service;

use FourPaws\CatalogBundle\Dto\Yandex\Feed;
use JMS\Serializer\SerializerInterface;
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

    public function getFeed()
    {
        // TODO: Implement getFeed() method.
    }
}
