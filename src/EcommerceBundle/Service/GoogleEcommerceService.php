<?php

namespace FourPaws\EcommerceBundle\Service;


use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Collection\ProductCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product as ProductModel;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Action;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Ecommerce;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\GoogleEcommerce;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Product;
use FourPaws\EcommerceBundle\Dto\GoogleEcommerce\Promotion;
use FourPaws\EcommerceBundle\Exception\InvalidArgumentException;
use FourPaws\EcommerceBundle\Utils\ArrayMapper;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;
use RuntimeException;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class GoogleEcommerceService
 *
 * @todo добавить общий класс/trait; добавить настройки с магией
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
     * @param ArrayMapper $mapper
     * @param array $data
     * @param string $type
     *
     * @return GoogleEcommerce
     *
     * @throws InvalidArgumentException
     */
    public function buildPromotionFromArray(ArrayMapper $mapper, array $data, string $type): GoogleEcommerce
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
                        ->setId($offer->getId())
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
     * Array mapper factory method
     *
     * @param array $map
     *
     * @return ArrayMapper
     */
    public function getArrayMapper(array $map): ArrayMapper
    {
        return new ArrayMapper($map);
    }
}
