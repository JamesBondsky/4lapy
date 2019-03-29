<?php

namespace FourPaws\CatalogBundle\Service;


use FourPaws\CatalogBundle\Exception\ArgumentException;
use FourPaws\CatalogBundle\Translate\ConfigurationInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use FourPaws\App\Application;

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
     * @var string
     */
    public $tmpFileName;
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
     * @param string                 $stockID
     *
     * If need to continue, return true. Else - false.
     *
     * @return boolean
     */
    abstract public function process(ConfigurationInterface $configuration, int $step, string $stockID = null): bool;

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
        $this->filesystem->dumpFile($file, $this->serializer->serialize($feed, 'xml'));
    }

    /**
     * @param        $data
     * @param string $file
     *
     * @throws IOException
     */
    public function publicFeedJson(array $data, string $file): void
    {
        $this->filesystem->dumpFile($file, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }

    /**
     * @return string
     */
    public function getStorageKey(): string
    {
        return \sprintf(
            '%s/%s/' . $this->tmpFileName,
            \sys_get_temp_dir(),
            Application::getInstance()->getEnvironment()
        );
    }
}
