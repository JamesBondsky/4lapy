<?php

namespace FourPaws\CatalogBundle\Service;


use FourPaws\CatalogBundle\Exception\ArgumentException;
use FourPaws\CatalogBundle\Translate\ConfigurationInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use FourPaws\App\Application;
use FourPaws\CatalogBundle\Dto\Feed\Feed as AbstractFeed;
use FourPaws\CatalogBundle\Translate\Configuration;
use FourPaws\Catalog\Query\OfferQuery;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\Catalog\Model\Category;

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
     * @var array
     */
    public $categoriesInProducts;

    /**
     * FeedService constructor.
     *
     * @param SerializerInterface $serializer
     * @param Filesystem $filesystem
     * @param string $context
     */
    public function __construct(SerializerInterface $serializer, Filesystem $filesystem, string $context)
    {
        $this->serializer = $serializer;
        $this->context = $context;
        $this->filesystem = $filesystem;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param int $step
     * @param string $stockID
     *
     * If need to continue, return true. Else - false.
     *
     * @return boolean
     */
    abstract public function process(ConfigurationInterface $configuration, int $step, string $stockID = null): bool;

    /**
     * @param Category $category
     * @param ArrayCollection $categoryCollection
     */
    abstract protected function addCategory(Category $category, ArrayCollection $categoryCollection): void;

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
        $this->filesystem->dumpFile($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
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

    /**
     * @param array $filter
     * @param int $offset
     * @param int $limit
     *
     * @return OfferCollection
     */
    protected function getOffers(array $filter, int $offset = 0, $limit = 500): OfferCollection
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return (new OfferQuery())->withFilter($filter)
            ->withNav([
                'nPageSize' => $limit,
                'iNumPage' => (int)\ceil(($offset + 1) / $limit),
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
        return (int)\ceil($offset / $limit);
    }

    /**
     * @param AbstractFeed $feed
     * @param Configuration $configuration
     * @param bool $withParents
     * @return YandexFeedService
     */
    protected function processCategories(AbstractFeed $feed, Configuration $configuration, $withParents = false): FeedService
    {
        $categories = new ArrayCollection();
        $categoriesTmp = new ArrayCollection();

        if (in_array(0, $configuration->getSectionIds())) {
            $filter = [
                'GLOBAL_ACTIVE' => 'Y'
            ];
        } else {
            $filter = [
                'ID' => $configuration->getSectionIds(),
                'GLOBAL_ACTIVE' => 'Y'
            ];
        }

        /**
         * @var CategoryCollection $parentCategories
         */
        $parentCategories = (new CategoryQuery())
            ->withFilter($filter)
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
            $categoriesTmp->set(
                $parentCategory->getId(),
                $parentCategory
            );

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

            /** @var Category $category */
            foreach ($childCategories as $category) {
                $this->addCategory($category, $categories);
                $categoriesTmp->set(
                    $category->getId(),
                    $category
                );
            }
        }

        if ($withParents) {
            $emptyParentCategoriesIds = array_diff($this->categoriesInProducts, array_keys($categoriesTmp->toArray()));
            foreach ($categoriesTmp as $category) {
                $parentCategoryId = $category->getIblockSectionId();
                if ($parentCategoryId !== null && $parentCategoryId !== 0 && !in_array($parentCategoryId, array_keys($categoriesTmp->toArray()))) {
                    $emptyParentCategoriesIds[$parentCategoryId] = $parentCategoryId;
                }
            }
            while (count($emptyParentCategoriesIds) > 0) {
                $emptyParentCategoriesIds = $this->addParentsCategory($emptyParentCategoriesIds, $categories, $categoriesTmp);
            }
            $iterator = $categories->getIterator();
            $iterator->uasort(function ($a, $b) {
                return ($a->getId() < $b->getId()) ? -1 : 1;
            });
            $categories = new ArrayCollection(iterator_to_array($iterator));
        }

        $feed->getShop()->setCategories($categories);

        return $this;
    }

    protected function addParentsCategory(array $emptyParentCategoriesIds, ArrayCollection $categories, ArrayCollection &$categoriesTmp): array
    {
        $result = [];
        $emptyParentCategories = (new CategoryQuery())
            ->withFilter([
                'ID' => $emptyParentCategoriesIds
            ])
            ->withOrder(['LEFT_MARGIN' => 'ASC'])
            ->exec();
        /** @var Category $category */
        foreach ($emptyParentCategories as $category) {
            $this->addCategory($category, $categories);
            $categoriesTmp->set(
                $category->getId(),
                $category
            );
            $parentCategoryId = $category->getIblockSectionId();
            if ($parentCategoryId !== null && $parentCategoryId !== 0 && !in_array($parentCategoryId, array_keys($categoriesTmp->toArray()))) {
                $result[$parentCategoryId] = $parentCategoryId;
            }
        }
        return $result;
    }
}
