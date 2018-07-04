<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Materials;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\UpdateResult;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\Catalog\Model\Offer;
use FourPaws\SapBundle\Dto\In\Offers\BarCode;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Enum\SapOfferProperty;
use FourPaws\SapBundle\Exception\CantCreateReferenceItem;
use FourPaws\SapBundle\Exception\LogicException;
use FourPaws\SapBundle\Exception\NotFoundBasicUomException;
use FourPaws\SapBundle\Exception\NotFoundDataManagerException;
use FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException;
use FourPaws\SapBundle\Exception\RuntimeException as SapRuntimeException;
use FourPaws\SapBundle\Repository\OfferRepository;
use FourPaws\SapBundle\Repository\ProductRepository;
use FourPaws\SapBundle\Service\ReferenceService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

/**
 * Class OfferService
 *
 * @package FourPaws\SapBundle\Service\Materials
 */
class OfferService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var ReferenceService
     */
    private $referenceService;

    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * OfferService constructor.
     *
     * @param ReferenceService $referenceService
     * @param OfferRepository $offerRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ReferenceService $referenceService,
        OfferRepository $offerRepository,
        ProductRepository $productRepository
    )
    {
        $this->referenceService = $referenceService;
        $this->offerRepository = $offerRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Material $material
     *
     * @throws RuntimeException
     * @throws NotFoundDataManagerException
     * @throws NotFoundBasicUomException
     * @throws CantCreateReferenceItem
     * @throws NotFoundReferenceRepositoryException
     * @throws LogicException
     * @throws SapRuntimeException
     * @return Offer
     */
    public function processMaterial(Material $material): Offer
    {
        $offer = $this->findByMaterial($material) ?: new Offer();
        $this->fillFromMaterial($offer, $material);

        return $offer;
    }

    /**
     * @param Offer $offer
     *
     * @return AddResult
     */
    public function create(Offer $offer): AddResult
    {
        return $this->offerRepository->create($offer);
    }

    /**
     * @param Offer $offer
     *
     * @return UpdateResult
     */
    public function update(Offer $offer): UpdateResult
    {
        return $this->offerRepository->update($offer);
    }

    /**
     * @param string $xmlId
     *
     * @throws RuntimeException
     * @return bool
     */
    public function deactivate(string $xmlId): bool
    {
        /** @var Offer $offer */
        /** @noinspection PhpAssignmentInConditionInspection */
        if ($offer = $this->offerRepository->findByXmlId($xmlId)) {
            $result = $this->offerRepository->setActive($offer->getId(), false);

            if ($result) {
                $this->log()->info(sprintf('Деактивирован оффер %s [%s]', $offer->getId(), $xmlId));
            }

            $product = $offer->getProduct();
            $activeOffers = $product->getOffers(false)->filter(function (Offer $offer) {
                return $offer->isActive();
            });
            if ($activeOffers->isEmpty() &&
                $this->productRepository->setActive($product->getId(), false)
            ) {
                $this->log()->info(sprintf('Деактивирован товар %s [%s]', $product->getId(), $product->getXmlId()));
            }

            return $result;
        }

        return true;
    }

    /**
     * @param Material $material
     *
     * @return null|IblockElement|Offer
     */
    protected function findByMaterial(Material $material)
    {
        return $this->offerRepository->findByXmlId($material->getOfferXmlId());
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     *
     * @throws RuntimeException
     * @throws NotFoundReferenceRepositoryException
     * @throws NotFoundDataManagerException
     * @throws LogicException
     * @throws CantCreateReferenceItem
     * @throws NotFoundBasicUomException
     * @return void
     */
    protected function fillFromMaterial(Offer $offer, Material $material): void
    {
        $this->fillFields($offer, $material);
        $this->fillProperties($offer, $material);
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     */
    protected function fillFields(Offer $offer, Material $material): void
    {
        $offer
            ->withActive(!$material->isNotUploadToIm())
            ->withName($material->getOfferName())
            ->withXmlId($material->getOfferXmlId());
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     *
     * @throws RuntimeException
     * @throws NotFoundReferenceRepositoryException
     * @throws NotFoundDataManagerException
     * @throws LogicException
     * @throws CantCreateReferenceItem
     * @throws NotFoundBasicUomException
     */
    protected function fillProperties(Offer $offer, Material $material): void
    {
        /**
         * @todo пока нет объединения по цвету
         * $offer->withColourCombination(
         * (string)$material->getProperties()->getPropertyValues(
         * SapOfferProperty::COLOUR_COMBINATION,
         * ['']
         * )->first()
         * );
         */
        $offer->withFlavourCombination(
            (string)$material->getProperties()->getPropertyValues(
                SapOfferProperty::FLAVOUR_COMBINATION,
                ['']
            )->first()
        );
        $offer->withMultiplicity($material->getCountInPack());
        $this->fillReferenceProperties($offer, $material);
        $this->fillBarCodes($offer, $material);
        $this->fillVolume($offer, $material);
    }

    /**
     * @param Offer    $offer
     * @param Material $material
     *
     * @throws NotFoundBasicUomException
     */
    protected function fillVolume(Offer $offer, Material $material): void
    {
        $offer->withVolume($material->getBasicUnitOfMeasure()->getVolume());
    }


    /**
     * @param Offer    $offer
     * @param Material $material
     *
     * @throws RuntimeException
     * @throws NotFoundReferenceRepositoryException
     * @throws NotFoundDataManagerException
     * @throws LogicException
     * @throws CantCreateReferenceItem
     */
    protected function fillReferenceProperties(Offer $offer, Material $material): void
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
    protected function fillBarCodes(Offer $offer, Material $material): void
    {
        $barcodes = $material->getAllBarcodes()->map(function (BarCode $barCode) {
            return $barCode->getValue();
        })->toArray();

        $offer->withBarcodes($barcodes);
    }
}
