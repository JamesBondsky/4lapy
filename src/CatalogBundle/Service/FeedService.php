<?php

namespace FourPaws\CatalogBundle\Service;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class FeedService
 *
 * Abstract class to feed creation (serializable, particiable, very funny)
 *
 * @package FourPaws\CatalogBundle\Service
 */
abstract class FeedService
{
    protected $feed;
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var string
     */
    protected $context;

    /**
     * FeedService constructor.
     *
     * @param SerializerInterface $serializer
     * @param Filesystem          $filesystem
     * @param string              $context
     */
    public function __construct(SerializerInterface $serializer, Filesystem $filesystem, string $context)
    {
        $this->serializer = $serializer;
        $this->context = $context;
    }

    abstract public function getFeed();
}
