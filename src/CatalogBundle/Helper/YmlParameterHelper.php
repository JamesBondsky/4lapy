<?php

namespace FourPaws\CatalogBundle\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Model\CatalogProduct;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\CatalogBundle\Dto\YmlOfferParameterInterface;
use FourPaws\CatalogBundle\Exception\YmlParameterCountException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class YmlParameterHelper
{
    /**
     * @var string
     */
    protected $parameterClass;

    /**
     * @var int
     */
    protected $maxCount;

    /**
     * @var int
     */
    protected $count;

    /**
     * YmlParameterHelper constructor.
     *
     * @param string   $parameterClass
     * @param int|null $maxCount
     *
     * @throws \LogicException
     */
    public function __construct(string $parameterClass, int $maxCount = null)
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        if (!is_a($parameterClass, YmlOfferParameterInterface::class, true)) {
            throw new \LogicException(\sprintf('Expected %s, got %s', YmlOfferParameterInterface::class, $parameterClass));
        }

        $this->parameterClass = $parameterClass;
        $this->maxCount = $maxCount;
    }

    /**
     * @param Offer $offer
     *
     * @return ArrayCollection
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function getOfferParameters(Offer $offer): ArrayCollection
    {
        $parameters = new ArrayCollection();

        try {
            $this->addOfferParameters($offer, $parameters);
            $this->addProductParameters($offer->getProduct(), $parameters);
            $this->addCatalogParameters($offer->getCatalogProduct(), $parameters);
        } catch (YmlParameterCountException $e) {
        }

        $this->count = 0;

        return $parameters;
    }

    /**
     * @param Offer           $offer
     * @param ArrayCollection $parameters
     *
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws YmlParameterCountException
     */
    protected function addOfferParameters(Offer $offer, ArrayCollection $parameters): void
    {
        if ($color = $offer->getColor()) {
            $this->addReferenceParameter($parameters, $color, 'Цвет');
        }

        if ($size = $offer->getClothingSize()) {
            $this->addReferenceParameter($parameters, $size, 'Размер');
        }

        if ($volume = $offer->getVolumeReference()) {
            $this->addReferenceParameter($parameters, $volume, 'Объем');
        }

        if ($packageType = $offer->getKindOfPacking()) {
            $this->addReferenceParameter($parameters, $packageType, 'Тип упаковки');
        }
    }

    /**
     * @param Product         $product
     * @param ArrayCollection $parameters
     *
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws YmlParameterCountException
     */
    protected function addProductParameters(Product $product, ArrayCollection $parameters): void
    {
        if ($petAges = $product->getPetAge()) {
            $this->addReferenceParameters($parameters, $petAges, 'Возраст питомца');
        }

        if ($petGender = $product->getPetGender()) {
            $this->addReferenceParameter($parameters, $petGender, 'Пол питомца');
        }

        if ($petSizes = $product->getPetSize()) {
            $this->addReferenceParameters($parameters, $petSizes, 'Размер питомца');
        }

        if ($consistence = $product->getConsistence()) {
            $this->addReferenceParameter($parameters, $consistence, 'Тип корма');
        }

        if ($country = $product->getCountry()) {
            $this->addReferenceParameter($parameters, $country, 'Страна-производитель');
        }

        if ($foodSpecification = $product->getFeedSpecification()) {
            $this->addReferenceParameter($parameters, $foodSpecification, 'Специальные показания');
        }

        if ($flavor = $product->getFlavour()) {
            $this->addReferenceParameters($parameters, $flavor, 'Вкус корма');
        }

        if ($materials = $product->getManufactureMaterials()) {
            $this->addReferenceParameters($parameters, $materials, 'Материал изготовления');
        }

        if ($petType = $product->getPetType()) {
            $this->addReferenceParameter($parameters, $petType, 'Вид животного');
        }

        if ($pharmaGroup = $product->getPharmaGroup()) {
            $this->addReferenceParameter($parameters, $pharmaGroup, 'Фармакологическая группа');
        }

        if ($purpose = $product->getPurpose()) {
            $this->addReferenceParameter($parameters, $purpose, 'Назначение');
        }

        if ($clothSeasons = $product->getClothesSeasons()) {
            $this->addReferenceParameters($parameters, $clothSeasons, 'Сезон');
        }
    }

    /**
     * @param CatalogProduct  $catalogProduct
     * @param ArrayCollection $parameters
     *
     * @throws YmlParameterCountException
     */
    protected function addCatalogParameters(CatalogProduct $catalogProduct, ArrayCollection $parameters): void
    {
        if ($weight = $catalogProduct->getWeight()) {
            $this->addParameter($parameters, 'Вес, г', $weight);
        }

        if ($length = $catalogProduct->getLength()) {
            $this->addParameter($parameters, 'Длина, мм', $length);
        }

        if ($width = $catalogProduct->getWidth()) {
            $this->addParameter($parameters, 'Ширина, мм', $width);
        }

        if ($height = $catalogProduct->getHeight()) {
            $this->addParameter($parameters, 'Высота, мм', $height);
        }
    }

    /**
     * @param  ArrayCollection              $parameters
     * @param Collection|HlbReferenceItem[] $collection
     * @param string                        $name
     *
     * @throws YmlParameterCountException
     */
    protected function addReferenceParameters(
        $parameters,
        $collection,
        string $name
    ): void
    {
        /** @var HlbReferenceItem $item */
        foreach ($collection as $item) {
            $this->addReferenceParameter($parameters, $item, $name);
        }
    }

    /**
     * @param ArrayCollection  $parameters
     * @param HlbReferenceItem $item
     * @param string           $name
     *
     * @throws YmlParameterCountException
     */
    protected function addReferenceParameter(
        ArrayCollection $parameters,
        HlbReferenceItem $item,
        string $name
    ): void
    {
        $this->addParameter($parameters, $name, $item->getName());
    }

    /**
     * @param ArrayCollection $parameters
     * @param string          $name
     * @param string          $value
     *
     * @throws YmlParameterCountException
     */
    protected function addParameter(ArrayCollection $parameters, string $name, string $value): void
    {
        if ($this->maxCount && $this->count >= $this->maxCount) {
            throw new YmlParameterCountException('Max parameter count exceeded');
        }

        /** @var YmlOfferParameterInterface $parameter */
        $parameter = new $this->parameterClass;
        $parameter->setName($name)
                  ->setValue($value);

        $parameters->add($parameter);
        $this->count++;
    }
}
