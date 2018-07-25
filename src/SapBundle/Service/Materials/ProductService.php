<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service\Materials;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\Entity\AddResult;
use Bitrix\Main\Entity\UpdateResult;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\Catalog\Model\Product;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Enum\SapProductField;
use FourPaws\SapBundle\Enum\SapProductProperty;
use FourPaws\SapBundle\Exception\NotFoundPropertyException;
use FourPaws\SapBundle\Repository\ProductRepository;
use FourPaws\SapBundle\Service\ReferenceService;

/**
 * Class ProductService
 * @package FourPaws\SapBundle\Service\Materials
 */
class ProductService
{
    /**
     * @var ReferenceService
     */
    private $referenceService;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * ProductService constructor.
     *
     * @param ReferenceService $referenceService
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ReferenceService $referenceService,
        ProductRepository $productRepository
    ) {
        $this->referenceService = $referenceService;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Material $material
     *
     * @throws \RuntimeException
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     * @throws IblockNotFoundException
     * @return Product
     */
    public function processMaterial(Material $material): Product
    {
        $product = $this->findByMaterial($material) ?: new Product();
        $this->fillProduct($product, $material);
        return $product;
    }

    /**
     * @param Product $product
     *
     * @return AddResult
     */
    public function create(Product $product): AddResult
    {
        return $this->productRepository->create($product);
    }

    /**
     * @param Product $product
     *
     * @return UpdateResult
     */
    public function update(Product $product): UpdateResult
    {
        return $this->productRepository->update($product);
    }

    /**
     * @throws IblockNotFoundException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     * @throws ConfigurationException
     * @throws SqlQueryException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws NotFoundPropertyException
     */
    public function deleteEmptyProducts()
    {
        foreach ($this->productRepository->getEmptyProducts() as $product) {
            $this->productRepository->delete($product);
        }
    }

    /**
     * @param Material $material
     *
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws IblockNotFoundException
     * @return null|Product
     */
    protected function findByMaterial(Material $material): ?Product
    {
        $product = $this->findByCombination(
            $material->getProperties()->getPropertyValues(
                SapProductProperty::PACKING_COMBINATION
            )->first()
        );
        return $product ?: $this->findByOfferWithoutCombination($material->getOfferXmlId());
    }

    /**
     * @param string $combination
     *
     * @return null|Product
     */
    protected function findByCombination(string $combination): ?Product
    {
        if (!$combination) {
            return null;
        }

        return $this->productRepository->findBy([
            'PROPERTY_PACKING_COMBINATION' => $combination,
        ], [], 1)->first() ?: null;
    }

    /**
     * @param string $xmlId
     *
     * @throws IblockNotFoundException
     * @return null|IblockElement|Product
     */
    protected function findByOfferWithoutCombination(string $xmlId)
    {
        $dbResult = \CIBlockElement::GetList(
            [],
            [
                '!PROPERTY_CML2_LINK' => false,
                'IBLOCK_ID'           => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
                'XML_ID'              => $xmlId,
            ],
            false,
            false,
            [
                'PROPERTY_CML2_LINK',
                'PROPERTY_CML2_LINK.PROPERTY_PACKING_COMBINATION',
            ]
        );
        $data = $dbResult->Fetch();
        $id = $data['PROPERTY_CML2_LINK_VALUE'] ?? 0;
        /**
         * Если найденный товар уже привязан к комбинации - игнорируем его
         * Поиск по комбинации осуществляется отдельно
         */
        if (!$id || $data['PROPERTY_CML2_LINK_PROPERTY_PACKING_COMBINATION_VALUE'] ?? 0) {
            return null;
        }
        /**
         * fix если по каким-то причинам товар оказался в комбинационном товаре, который не имеет признака комбинации
         */
        $countOther = (int)\CIBlockElement::GetList(
            [],
            [
                'PROPERTY_CML2_LINK' => $id,
                'IBLOCK_ID'          => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
                '!XML_ID'            => $xmlId,
            ],
            []
        );
        if ($countOther > 0) {
            return null;
        }


        return $this->productRepository->find($id);
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
    protected function fillProduct(Product $product, Material $material): void
    {
        $this->fillFields($product, $material);
        $this->fillProperties($product, $material);
    }

    /**
     * @param Product  $product
     * @param Material $material
     */
    protected function fillFields(Product $product, Material $material): void
    {
        if ($material->getProductName()) {
            $product->withName($material->getProductName());
        }

        if (!$product->getName()) {
            $product->withName($material->getProductName() ?: $material->getOfferName());
        }

        $product->withActive(!$material->isNotUploadToIm());

        if (!$product->getId() && mb_strpos($material->getOfferXmlId(), '1') === 0) {
            /**
             * По умолчанию создающиеся товары должны быть деактивированными, если это не подарки
             */
            $product->withActive(false);
        }
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
    protected function fillProperties(Product $product, Material $material): void
    {
        $product
            ->withStm(
                (int)$material->getProperties()->getPropertyValues(
                    SapProductProperty::STM,
                    [0]
                )->first() === 1
            )
            ->withLicenseRequired(
                (int)$material->getProperties()->getPropertyValues(
                    SapProductProperty::LICENSE,
                    [0]
                )->first() === 1
            )
            ->withLowTemperatureRequired(
                (int)$material->getProperties()->getPropertyValues(
                    SapProductProperty::LOW_TEMPERATURE,
                    [0]
                )->first() === 1
            )
            ->withIsFood(
                (int)$material->getProperties()->getPropertyValues(
                    SapProductProperty::FOOD,
                    [0]
                )->first() === 1
            )
            ->withPackingCombination(
                (string)$material->getProperties()->getPropertyValues(
                    SapProductProperty::PACKING_COMBINATION,
                    ['']
                )->first()
            )
            ->withApplyAutoSort(!$product->getId());
        $this->fillReferenceProperties($product, $material);
        $this->fillCountry($product, $material);
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
    protected function fillReferenceProperties(Product $product, Material $material): void
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
     * @throws \RuntimeException
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     */
    protected function fillCountry(Product $product, Material $material): void
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
