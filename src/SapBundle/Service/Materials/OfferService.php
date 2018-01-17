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
     * @return null|Offer
     */
    public function findByMaterial(Material $material)
    {
        return $this->offerRepository->findByXmlId($material->getOfferXmlId());
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     *
     * @throws \RuntimeException
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     * @throws \FourPaws\SapBundle\Exception\NotFoundBasicUomException
     * @return void
     */
    public function fillFromMaterial(Offer $offer, Material $material)
    {
        $this->fillFields($offer, $material);
        $this->fillProperties($offer, $material);
        $this->fillOfferCatalogProduct($offer, $material);
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     */
    protected function fillFields(Offer $offer, Material $material)
    {
        $offer
            ->withActive(!$material->isNotUploadToIm())
            ->withName($material->getOfferName())
            ->withXmlId($material->getOfferXmlId())
            ->withCode($offer->getCode() ?: $this->slugify->slugify($material->getOfferName()));
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     *
     * @throws \RuntimeException
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     * @throws \FourPaws\SapBundle\Exception\NotFoundBasicUomException
     */
    protected function fillProperties(Offer $offer, Material $material)
    {
        /**
         * @todo На данный момент нет описания полей по SAP
         * $offer->withFlavourCombination();
         * $offer->withColourCombination();
         */
        $offer->withMultiplicity($material->getCountInPack());
        $this->fillReferenceProperties($offer, $material);
        $this->fillBarCodes($offer, $material);
        $this->fillVolume($offer, $material);
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     *
     * @throws \FourPaws\SapBundle\Exception\NotFoundBasicUomException
     */
    protected function fillOfferCatalogProduct(Offer $offer, Material $material)
    {
        $catalogProduct = $offer->getId() ? $offer->getCatalogProduct() : new CatalogProduct();
        $catalogProduct = $catalogProduct ?: new CatalogProduct();

        $basicUom = $material->getBasicUnitOfMeasure();
        $catalogProduct
            ->setWidth($basicUom->getWidth() * 1000)
            ->setHeight($basicUom->getHeight() * 1000)
            ->setLength($basicUom->getLength() * 1000)
            ->setWeight($basicUom->getGrossWeight() * 1000);
        $offer->withCatalogProduct($catalogProduct);
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     *
     * @throws \FourPaws\SapBundle\Exception\NotFoundBasicUomException
     */
    protected function fillVolume(Offer $offer, Material $material)
    {
        $offer->withVolume($material->getBasicUnitOfMeasure()->getVolume());
    }


    /**
     * @param Offer    $offer
     * @param Material $material
     *
     * @throws \RuntimeException
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     */
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
