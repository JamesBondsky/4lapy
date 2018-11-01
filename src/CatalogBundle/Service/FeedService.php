<?php

namespace FourPaws\CatalogBundle\Service;


use FourPaws\CatalogBundle\Exception\ArgumentException;
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

    /**
     * @todo set with client
     *
     * @param string $key
     * @param        $data
     *
     * @throws IOException
     * @throws ArgumentException
     */
    public function saveFeed(string $key, $data): void
    {
        if (!$data instanceof $this->context) {
            throw new ArgumentException('Wrong save feed context');
        }

        $this->filesystem->dumpFile($key, $this->serializer->serialize($data, 'xml'));
    }

    /**
     * @todo set with client
     *
     * @param string $key
     *
     * @return mixed
     */
    public function loadFeed(string $key)
    {
        return $this->serializer->deserialize(\file_get_contents($key), $this->context, 'xml');
    }

    /**
     * @todo set with client
     *
     * @param string $key
     *
     * @throws IOException
     */
    public function clearFeed(string $key): void
    {
        $this->filesystem->remove($key);
    }

    /**
     * @param        $feed
     * @param string $file
     *
     * @throws IOException
     */
    public function publicFeed($feed, string $file): void
    {
        //TODO hotfix, because JMS serializer change & to &amp;
        $this->filesystem->dumpFile($file, str_replace('&amp;','&',$this->serializer->serialize($feed, 'xml')));
    }
}
