<?php

namespace FourPaws\SapBundle\Service\Materials;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Cocur\Slugify\SlugifyInterface;
use FourPaws\BitrixOrm\Model\CatalogProduct;
use FourPaws\Catalog\Model\Offer;
use FourPaws\SapBundle\Dto\In\Offers\BarCode;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Dto\In\Offers\UnitOfMeasurement;
use FourPaws\SapBundle\Enum\SapOfferProperty;
use FourPaws\SapBundle\Exception\RuntimeException;
use FourPaws\SapBundle\Service\ReferenceService;
use Psr\Log\LoggerAwareInterface;

class OfferService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var SlugifyInterface
     */
    private $slugify;

    /**
     * @var ReferenceService
     */
    private $referenceService;

    public function __construct(
        SlugifyInterface $slugify,
        ReferenceService $referenceService
    ) {
        $this->slugify = $slugify;
        $this->referenceService = $referenceService;
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     *
     * @return void
     */
    public function fillFromMaterial(Offer $offer, Material $material)
    {
        $offer
            ->withActive($material->isNotUploadToIm())
            ->withName($material->getOfferName())
            ->withBarcodes($this->getBarcodes($material)->toArray())
            ->withXmlId($material->getOfferXmlId())
            ->withCode($offer->getCode() ?: $this->slugify->slugify($material->getOfferName()))
            ->withMultiplicity($material->getCountInPack());

        /**
         * @todo На данный момент не описания полей по SAP
         * $offer->withFlavourCombination();
         * $offer->withColourCombination();
         */

        $this->fillVolume($offer, $material);
        $this->fillReferenceProperties($offer, $material);
    }

    protected function getCatalogProduct(Offer $offer, Material $material)
    {
        $catalogProduct = $offer->getId() ? $offer->getCatalogProduct() : new CatalogProduct();
        $basicUom = $this->getBasicUnitOfMeasure($material);
        $catalogProduct
            ->setWidth($basicUom->getWidth() * 1000)
            ->setHeight($basicUom->getHeight() * 1000)
            ->setLength($basicUom->getLength() * 1000)
            ->setWeight($basicUom->getGrossWeight() * 1000);
    }


    protected function fillVolume(Offer $offer, Material $material)
    {
        $offer->withVolume($this->getBasicUnitOfMeasure($material)->getVolume());
    }

    protected function getBarcodes(Material $material)
    {
        $collection = $material->getUnitsOfMeasure()->map(function (UnitOfMeasurement $unitOfMeasurement) {
            return $unitOfMeasurement->getBarCodes()->map(function (BarCode $barcode) {
                return $barcode->getValue();
            });
        });
        foreach ($collection as $key => $unitBarcodes) {
            if (\is_array($unitBarcodes)) {
                foreach ($unitBarcodes as $barcode) {
                    $collection->add($barcode);
                }
                $collection->remove($key);
            }
        }
        return $collection->filter(function ($string) {
            return \is_string($string) && $string;
        });
    }

    /**
     * @param Material $material
     *
     * @throws \FourPaws\SapBundle\Exception\RuntimeException
     * @return UnitOfMeasurement
     */
    protected function getBasicUnitOfMeasure(Material $material): UnitOfMeasurement
    {
        $basicCode = $material->getBasicUnitOfMeasurementCode() ?: Material::DEFAULT_BASE_UNIT_OF_MEASUREMENT_CODE;
        $basicUom = $material
            ->getUnitsOfMeasure()
            ->filter(function (UnitOfMeasurement $unitOfMeasurement) use ($basicCode) {
                return $unitOfMeasurement->getAlternativeUnitCode() === $basicCode;
            })->current();

        if ($basicUom) {
            return $basicUom;
        }
        throw new RuntimeException(sprintf('No basic Unity Of measure for material %s', $material->getOfferXmlId()));
    }

    protected function fillReferenceProperties(Offer $offer, Material $material)
    {
        $offer
            ->withColourXmlId($this->referenceService->getPropertyBitrixValue(SapOfferProperty::COLOUR, $material))
            ->withKindOfPackingXmlId($this->referenceService->getPropertyBitrixValue(
                SapOfferProperty::KIND_OF_PACKING,
                $material
            ))
            ->withClothingSizeXmlId($this->referenceService->getPropertyBitrixValue(
                SapOfferProperty::CLOTHING_SIZE,
                $material
            ))
            ->withVolumeReferenceXmlId($this->referenceService->getPropertyBitrixValue(
                SapOfferProperty::VOLUME,
                $material
            ))
            ->withSeasonYearXmlId($this->referenceService->getPropertyBitrixValue(
                SapOfferProperty::SEASON_YEAR,
                $material
            ));
    }
}
