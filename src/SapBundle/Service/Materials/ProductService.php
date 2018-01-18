<?php

namespace FourPaws\SapBundle\Service\Materials;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Enum\SapProductField;
use FourPaws\SapBundle\Enum\SapProductProperty;
use FourPaws\SapBundle\Repository\BrandRepository;
use FourPaws\SapBundle\Service\ReferenceService;

class ProductService
{
    /**
     * @var ReferenceService
     */
    private $referenceService;
    /**
     * @var BrandRepository
     */
    private $brandRepository;

    public function __construct(ReferenceService $referenceService, BrandRepository $brandRepository)
    {
        $this->referenceService = $referenceService;
        $this->brandRepository = $brandRepository;
    }

    public function processMaterial(Material $material)
    {
        $product = $this->findByMaterial($material) ?: new Product();
        $this->fillProduct($product, $material);
        return $product;
    }

    /**
     * @param Material $material
     *
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws IblockNotFoundException
     * @return null|Product
     */
    protected function findByMaterial(Material $material)
    {
        $product = $this->findByOffer($material->getOfferXmlId());
        $product = $product ?: $this->findByCombination(
            $material->getProperties()->getPropertyValues(
                SapProductProperty::PACKING_COMBINATION
            )->first()
        );
        return $product;
    }

    /**
     * @param Product  $product
     * @param Material $material
     *
     * @throws \RuntimeException
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     * @throws \FourPaws\SapBundle\Exception\LogicException
     */
    protected function fillProduct(Product $product, Material $material)
    {
        $this->fillFields($product, $material);
        $this->fillProperties($product, $material);
    }

    /**
     * @param string $combination
     *
     * @return null|Product
     */
    protected function findByCombination(string $combination)
    {
        if (!$combination) {
            return null;
        }

        return (new ProductQuery())
            ->withFilter([
                'PROPERTY_PACKING_COMBINATION' => $combination,
            ])
            ->exec()
            ->current();
    }

    /**
     * @param string $xmlId
     *
     * @throws IblockNotFoundException
     * @return null|Product
     */
    protected function findByOffer(string $xmlId)
    {
        $dbResult = \CIBlockElement::GetList([], [
            '!PROPERTY_CML2_LINK' => false,
            'IBLOCK_ID'           => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
            'XML_ID'              => $xmlId,
        ], false, false, ['PROPERTY_CML2_LINK']);
        $data = $dbResult->Fetch();
        $id = $data['PROPERTY_CML2_LINK_VALUE'] ?? 0;
        if ($id) {
            return (new ProductQuery())->withFilter(['ID' => $id])->exec()->first();
        }
        return null;
    }

    /**
     * @param Product  $product
     * @param Material $material
     */
    protected function fillFields(Product $product, Material $material)
    {
        $brand = $this->brandRepository->getOrCreate($material->getBrandCode(), $material->getBrandName());
        $product
            ->withName($material->getProductName() ?: $material->getOfferName())
            ->withBrandId($brand ? $brand->getId() : 0);
    }

    /**
     * @param Product  $product
     * @param Material $material
     *
     * @throws \RuntimeException
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     * @throws \FourPaws\SapBundle\Exception\LogicException
     */
    protected function fillProperties(Product $product, Material $material)
    {
        $product
            ->withSTM(
                (int)$material->getProperties()->getPropertyValues(
                    SapProductProperty::STM,
                    [1]
                )->first() === 1
            )
            ->withLicenseRequired(
                (int)$material->getProperties()->getPropertyValues(
                    SapProductProperty::LICENSE,
                    [1]
                )->first() === 1
            )
            ->withLowTemperatureRequired(
                (int)$material->getProperties()->getPropertyValues(
                    SapProductProperty::LOW_TEMPERATURE,
                    [1]
                )->first() === 1
            )
            ->withIsFood(
                (int)$material->getProperties()->getPropertyValues(
                    SapProductProperty::FOOD,
                    [1]
                )->first() === 1
            );
        $this->fillReferenceProperties($product, $material);
        $this->fillCountry($product, $material);
        /**
         * @todo fields
         */
    }

    /**
     * @param Product  $product
     * @param Material $material
     *
     * @throws \RuntimeException
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     */
    protected function fillReferenceProperties(Product $product, Material $material)
    {
        $product
            ->withForWhoXmlIds($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::FOR_WHO,
                $material,
                true
            ))
            ->withTradeNameXmlIds($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::TRADE_NAME,
                $material,
                true
            ))
            ->withManagersOfCategoryXmlIds($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::MANAGER_OF_CATEGORY,
                $material,
                true
            ))
            ->withManufactureMaterialsXmlIds($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::MANUFACTURE_MATERIAL,
                $material,
                true
            ))
            ->withMakersXmlIds($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::MAKER,
                $material,
                true
            ))
            ->withPetSizeXmlIds($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::SIZE_OF_THE_ANIMAL_BIRD,
                $material,
                true
            ))
            ->withClothesSeasonsXmlIds($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::SEASON_CLOTHES,
                $material,
                true
            ))
            ->withPurposeXmlId($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::PURPOSE,
                $material
            ))
            ->withSapCategoryXmlId($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::CATEGORY,
                $material
            ))
            ->withPetAgeXmlIds($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::ANIMALS_AGE,
                $material,
                true
            ))
            ->withProductFormsXmlIds($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::PRODUCT_FORM,
                $material,
                true
            ))
            ->withPetTypeXmlId($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::KIND_OF_ANIMAL,
                $material
            ))
            ->withPharmaGroupXmlId($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::PHARMA_GROUP,
                $material
            ))
            ->withFeedSpecificationXmlId($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::FEED_SPECIFICATION,
                $material
            ))
            ->withFlavourXmlIds($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::FLAVOUR,
                $material,
                true
            ))
            ->withFeaturesOfIngredientsXmlIds($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::FEATURES_OF_INGREDIENTS,
                $material,
                true
            ))
            ->withPetBreedXmlId($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::BREED_OF_ANIMAL,
                $material
            ))
            ->withPetGenderXmlId($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::GENDER_OF_ANIMAL,
                $material
            ))
            ->withConsistenceXmlId($this->referenceService->getPropertyBitrixValue(
                SapProductProperty::CONSISTENCE,
                $material
            ));
    }

    /**
     * @param Product  $product
     * @param Material $material
     *
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     */
    protected function fillCountry(Product $product, Material $material)
    {
        $product->withCountryXmlId('');
        if ($material->getCountryOfOriginCode() && $material->getCountryOfOriginName()) {
            $country = $this->referenceService->getOrCreate(
                SapProductField::COUNTRY,
                $material->getCountryOfOriginCode(),
                $material->getCountryOfOriginName()
            );
            $product->withCountryXmlId($country->getXmlId());
        }
    }
}
