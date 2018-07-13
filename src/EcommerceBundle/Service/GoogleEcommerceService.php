<?php

namespace FourPaws\EcommerceBundle\Service;

use CDBResult;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product as ProductModel;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Action;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\ActionField;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Ecommerce;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\GoogleEcommerce;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Product;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Promotion;
use FourPaws\EcommerceBundle\Exception\InvalidArgumentException;
use FourPaws\EcommerceBundle\Mapper\ArrayMapper;
use FourPaws\EcommerceBundle\Mapper\ArrayMapperInterface;
use FourPaws\EcommerceBundle\Preset\PresetInterface;
use FourPaws\EcommerceBundle\Storage\KeyValueStaticStorage;
use InvalidArgumentException as MainInvalidArgumentException;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;
use RuntimeException;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class GoogleEcommerceService
 *
 * @todo add base ecommerce service class
 * @todo add ua settings
 * @todo add ga/gtm render
 * @todo add render configuration
 * @todo move products into preset
 *
 * @package FourPaws\EcommerceBundle\Service
 */
class GoogleEcommerceService implements ScriptRenderedInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var EngineInterface
     */
    private $renderer;
    /**
     * @var ArrayTransformerInterface
     */
    private $arrayTransformer;

    /**
     * GoogleEcommerceService constructor.
     *
     * @param SerializerInterface $serializer
     * @param ArrayTransformerInterface $arrayTransformer
     * @param EngineInterface $renderer
     */
    public function __construct(SerializerInterface $serializer, ArrayTransformerInterface $arrayTransformer, EngineInterface $renderer)
    {
        $this->serializer = $serializer;
        $this->renderer = $renderer;
        $this->arrayTransformer = $arrayTransformer;
    }

    /**
     * @param object $data
     * @param bool $addScriptTag
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function renderScript($data, bool $addScriptTag): string
    {
        /** @noinspection PhpParamsInspection */
        $data = $this->serializer->serialize($data, 'json');

        return \trim($this->renderer->render('EcommerceBundle:GoogleEcommerce:inline.script.php', \compact('data', 'addScriptTag')));
    }

    /**
     * @param object $data
     * @param string $presetName
     * @param bool $addScriptTag
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function renderPreset($data, string $presetName, bool $addScriptTag): string
    {
        /** @noinspection PhpParamsInspection */
        $data = $this->serializer->serialize($data, 'json');

        return \trim($this->renderer->render('EcommerceBundle:GoogleEcommerce:preset.inline.script.php', \compact('data', 'presetName', 'addScriptTag')));
    }

    /**
     * @param ArrayMapperInterface $mapper
     * @param array $data
     * @param string $type
     *
     * @return GoogleEcommerce
     *
     * @throws InvalidArgumentException
     */
    public function buildPromotionFromArray(ArrayMapperInterface $mapper, array $data, string $type): GoogleEcommerce
    {
        $ecommerce = (new GoogleEcommerce())->setEcommerce(new Ecommerce());

        $promotions = \array_map(function ($promotion) {
            return $this->arrayTransformer->fromArray($promotion, Promotion::class);
        }, $mapper->mapCollection($data));

        try {
            $ecommerce->getEcommerce()->{$type} = (new Action())->setPromotions(new ArrayCollection($promotions));
        } catch (\Throwable $e) {
            throw new InvalidArgumentException(\sprintf(
                'Unsupported ecommerce type %s',
                $type
            ));
        }

        return $ecommerce;
    }

    /**
     * @param ProductCollection $collection
     * @param string $list
     *
     * @return ArrayCollection
     */
    public function buildProductsFromProductsCollection(ProductCollection $collection, string $list = ''): ArrayCollection
    {
        $productCollection = new ArrayCollection();

        $collection->map(function (ProductModel $product) use ($productCollection, $list) {
            $product->getOffers()->map(function (Offer $offer) use ($productCollection, $product, $list) {
                $productCollection->add(
                    (new Product())
                        ->setId($offer->getXmlId())
                        ->setName($offer->getName())
                        ->setBrand($product->getBrandName())
                        ->setPrice($offer->getPrice())
                        ->setCategory(\implode('|', \array_reverse($product->getFullPathCollection()->map(function (Category $category) {
                            return $category->getName();
                        })->toArray())))
                        ->setList($list)
                        ->setPosition($productCollection->count() + 1)
                );
            });
        });

        return $productCollection;
    }

    /**
     * @param OfferCollection $collection
     * @param string $list
     *
     * @return ArrayCollection
     */
    public function buildProductsFromOfferCollection(OfferCollection $collection, string $list = ''): ArrayCollection
    {
        $productCollection = new ArrayCollection();

        $collection->map(function (Offer $offer) use ($productCollection, $list) {
            $productCollection->add(
                (new Product())
                    ->setId($offer->getId())
                    ->setName($offer->getName())
                    ->setBrand($offer->getProduct()->getBrandName())
                    ->setPrice($offer->getPrice())
                    ->setCategory(\implode('|', \array_reverse($offer->getProduct()->getFullPathCollection()->map(function (Category $category) {
                        return $category->getName();
                    })->toArray())))
                    ->setList($list)
                    ->setPosition($productCollection->count() + 1)
            );
        });

        return $productCollection;
    }

    /**
     * @param ProductCollection $collection
     * @param string $list
     *
     * @return GoogleEcommerce
     */
    public function buildImpressionsFromProductCollection(ProductCollection $collection, string $list = ''): GoogleEcommerce
    {
        $ecommerce = (new GoogleEcommerce())->setEcommerce(new Ecommerce());
        $ecommerce->getEcommerce()
            ->setCurrencyCode('RUB')
            ->setImpressions($this->buildProductsFromProductsCollection($collection, $list));

        return $ecommerce;
    }

    /**
     * @param Offer $offer
     * @param string $list
     *
     * @return GoogleEcommerce
     */
    public function buildDetailFromOffer(Offer $offer, string $list = ''): GoogleEcommerce
    {
        $offerCollection = new OfferCollection(new CDBResult());
        $offerCollection->add($offer);

        return (new GoogleEcommerce())
            ->setEcommerce(
                (new Ecommerce())
                    ->setCurrencyCode('RUB')
                    ->setDetail(
                        (new Action())
                            ->setActionField(
                                (new ActionField())
                                    ->setList($list)
                            )
                            ->setProducts($this->buildProductsFromOfferCollection($offerCollection))
                    )
            );
    }

    /**
     * @param Offer $offer
     * @param string $list
     *
     * @return GoogleEcommerce
     */
    public function buildClickFromOffer(Offer $offer, string $list = ''): GoogleEcommerce
    {
        $offerCollection = new OfferCollection(new CDBResult());
        $offerCollection->add($offer);

        return (new GoogleEcommerce())
            ->setEcommerce(
                (new Ecommerce())
                    ->setCurrencyCode('RUB')
                    ->setClick(
                        (new Action())
                            ->setActionField(
                                (new ActionField())
                                    ->setList($list)
                            )
                            ->setProducts($this->buildProductsFromOfferCollection($offerCollection))
                    )
            );
    }

    /**
     * @param array $offerList
     * @param string $list
     *
     * @return GoogleEcommerce
     */
    public function buildImpressionsFromOfferArray(array $offerList, string $list = ''): GoogleEcommerce
    {
        $storage = KeyValueStaticStorage::getInstance();
        $collection = new OfferCollection(new CDBResult());

        foreach ($offerList as $rawOffer) {
            $key = \sprintf(
                'offer_%d',
                $rawOffer['ID']
            );
            $offer = $storage->get($key);

            if ($offer) {
                $collection->add($offer);
            } else {
                $offer = OfferQuery::getById($rawOffer['ID']);

                if (!$offer) {
                    continue;
                }

                $collection->add($offer);
                $storage->set($key, $offer);
            }
        }

        $ecommerce = (new GoogleEcommerce())->setEcommerce(new Ecommerce());
        $ecommerce->getEcommerce()
            ->setCurrencyCode('RUB')
            ->setImpressions($this->buildProductsFromOfferCollection($collection, $list));

        return $ecommerce;
    }

    /**
     * Array mapper factory method
     *
     * @todo move mapper class to configuration
     *
     * @param array $map
     *
     * @return ArrayMapperInterface
     */
    public function getArrayMapper(array $map): ArrayMapperInterface
    {
        return new ArrayMapper($map);
    }

    /**
     * @param string $interfaceClass
     * @return PresetInterface
     *
     * @throws MainInvalidArgumentException
     */
    public function getPreset(string $interfaceClass): PresetInterface
    {
        /**
         * @todo - presetRepository
         */
        $class = $this->presetRepository->get($interfaceClass);

        /**
         * @todo move to repo
         */
        if (!$class instanceof PresetInterface) {
            throw new MainInvalidArgumentException(\sprintf(
                'Class %s must be intance of PresetInterface',
                $interfaceClass
            ));
        }

        return $class;
    }
}
