<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service;

use Bitrix\Main\Application;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Dto\In\Offers\Materials;
use FourPaws\SapBundle\Dto\In\Offers\PropertyValue;
use FourPaws\SapBundle\Enum\OfferProperty;
use FourPaws\SapBundle\Repository\BrandRepository;

class ProductDetailService
{
    /**
     * @var \Bitrix\Main\DB\Connection
     */
    protected $connection;

    /**
     * @var \CIBlockElement
     */
    protected $iblockElement;
    /**
     * @var BrandRepository
     */
    private $brandRepository;

    public function __construct(BrandRepository $brandRepository)
    {
        $this->connection = Application::getConnection();
        $this->iblockElement = new \CIBlockElement();
        $this->brandRepository = $brandRepository;
    }

    public function processMaterials(Materials $materials)
    {
        /**
         * @todo Обработка ошибок
         * @todo Транзакционность импорта в рамках одного sku
         */
        foreach ($materials->getMaterials() as $material) {
            $this->processMaterial($material);
        }
    }

    public function processMaterial(Material $material)
    {
        $offer = $this->findOffer($material->getOfferXmlId());
        /**
         * Деактивируем не выгружаемые
         */
        if ($material->isNotUploadToIm()) {
            if ($offer) {
                $this->iblockElement->Update($offer->getId(), [
                    'ACTIVE' => 'N',
                ]);
            }
            return;
        }

        $product = $this->findProduct($material, $offer);
        $brand = $this->brandRepository->getOrCreate($material->getBrandCode(), $material->getBrandName());

        if (!$product) {
            $product = new Product();
            $product
                ->withXmlId($material->getOfferXmlId())
                ->withName($material->getOfferName())
                ->withCode();
        }
    }

    /**
     * @param string $xmlId
     *
     * @return null|Offer
     */
    protected function findOffer(string $xmlId)
    {
        $query = new OfferQuery();
        $query->withFilter(array_merge($query->getBaseFilter(), [
            'XML_ID' => $xmlId,
        ]));
        return $query->exec()->first();
    }

    /**
     * @param Material   $material
     * @param null|Offer $offer
     *
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @return null|Product
     */
    protected function findProduct(Material $material, Offer $offer = null)
    {
        $query = new ProductQuery();
        $query->withFilter($query->getBaseFilter());
        /**
         * Если есть объединение по упаковке - ищем продукт по объединению
         * @var PropertyValue $value
         */
        if (
            ($packingProperty = $material->getProperties()->getProperty(OfferProperty::PACKING_COMBINATION)) &&
            ($value = $packingProperty->getValues()->first()) &&
            $value->getCode()
        ) {
            $query->withFilterParameter('PROPERTY_PACKING_COMBINATION', $value->getCode());
            return $query->exec()->current();
        }

        /**
         * Если нет объединения и уже получен оффер
         */
        if ($offer && $offer->getCml2Link()) {
            return $offer->getProduct();
        }

        return null;
    }
}
