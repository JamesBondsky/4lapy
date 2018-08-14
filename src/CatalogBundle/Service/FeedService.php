<?php

namespace FourPaws\CatalogBundle\Service;


use FourPaws\CatalogBundle\Translate\ConfigurationInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
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
     * @var Filesystem
     */
    private $filesystem;

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
        $this->filesystem = $filesystem;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param int                    $step
     *
     * If need to continue, return true. Else - false.
     *
     * @return boolean
     */
    abstract public function process(ConfigurationInterface $configuration, int $step): bool;

    public function saveFeed()
    {

    }

    /**
     * @return mixed
     */
    public function loadFeed()
    {
        $mixed = null;

        return $mixed;
    }

    public function clearFeed()
    {

    }

    /**
     * @param        $feed
     * @param string $file
     *
     * @throws IOException
     */
    public function publicFeed($feed, string $file): void
    {
        $this->filesystem->dumpFile(\sprintf(
            '%s%s',
            \getcwd(),
            $file
        ), $this->serializer->serialize($feed, 'xml'));
    }
}
