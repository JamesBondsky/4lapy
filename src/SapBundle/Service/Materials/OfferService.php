<?php

namespace FourPaws\SapBundle\Service\Materials;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Cocur\Slugify\SlugifyInterface;
use FourPaws\BitrixOrm\Model\CatalogProduct;
use FourPaws\Catalog\Model\Offer;
use FourPaws\SapBundle\Dto\In\Offers\BarCode;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Enum\SapOfferProperty;
use FourPaws\SapBundle\Repository\OfferRepository;
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
    /**
     * @var OfferRepository
     */
    private $offerRepository;

    public function __construct(
        SlugifyInterface $slugify,
        ReferenceService $referenceService,
        OfferRepository $offerRepository
    ) {
        $this->slugify = $slugify;
        $this->referenceService = $referenceService;
        $this->offerRepository = $offerRepository;
    }

    /**
     * @param Material $material
     *
     * @return Offer
     */
    public function getByMaterial(Material $material): Offer
    {
        $offer = $this->offerRepository->findByXmlId($material->getOfferXmlId()) ?: new Offer();
        $this->fillFromMaterial($offer, $material);
        return $offer;
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
            ->withActive(!$material->isNotUploadToIm())
            ->withName($material->getOfferName())
            ->withXmlId($material->getOfferXmlId())
            ->withCode($offer->getCode() ?: $this->slugify->slugify($material->getOfferName()))
            ->withMultiplicity($material->getCountInPack());

        /**
         * @todo На данный момент нет описания полей по SAP
         * $offer->withFlavourCombination();
         * $offer->withColourCombination();
         */

        /**
         * @todo Brand!
         */

        $this->fillBarCodes($offer, $material);
        $this->fillVolume($offer, $material);
        $this->fillReferenceProperties($offer, $material);
        $this->fillOfferCatalogProduct($offer, $material);
    }

    protected function fillOfferCatalogProduct(Offer $offer, Material $material)
    {
        $catalogProduct = $offer->getId() ? $offer->getCatalogProduct() : new CatalogProduct();
        $basicUom = $material->getBasicUnitOfMeasure();
        $catalogProduct
            ->setWidth($basicUom->getWidth() * 1000)
            ->setHeight($basicUom->getHeight() * 1000)
            ->setLength($basicUom->getLength() * 1000)
            ->setWeight($basicUom->getGrossWeight() * 1000);
        $offer->withCatalogProduct($catalogProduct);
    }

    protected function fillVolume(Offer $offer, Material $material)
    {
        $offer->withVolume($material->getBasicUnitOfMeasure()->getVolume());
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

    /**
     * @param Offer    $offer
     * @param Material $material
     */
    protected function fillBarCodes(Offer $offer, Material $material)
    {
        $barcodes = $material->getAllBarcodes()->map(function (BarCode $barCode) {
            return $barCode->getValue();
        })->toArray();
        $offer->withBarcodes($barcodes);
    }
}
