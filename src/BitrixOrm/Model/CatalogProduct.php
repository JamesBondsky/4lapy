<?php

namespace FourPaws\BitrixOrm\Model;

use FourPaws\BitrixOrm\Model\Exceptions\CatalogProductNotFoundException;
use FourPaws\BitrixOrm\Model\Interfaces\ActiveReadModelInterface;
use FourPaws\BitrixOrm\Query\CatalogProductQuery;

class CatalogProduct implements ActiveReadModelInterface
{
    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var double
     */
    protected $weight = 0;

    /**
     * @var double
     */
    protected $height = 0;

    /**
     * @var double
     */
    protected $width = 0;

    /**
     * @var double
     */
    protected $length = 0;

    /**
     * Код инфоблока товара
     *
     * @var int
     */
    protected $productIblockId = 0;

    /**
     * Внешний код товара
     *
     * @var string
     */
    protected $productXmlId = '';

    /**
     * Название товара
     *
     * @var string
     */
    protected $productName = '';

    /**
     * ModelInterface constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        $this
            ->setId((int)$fields['ID'])
            ->setWeight((float)$fields['WEIGHT'])
            ->setHeight((float)$fields['HEIGHT'])
            ->setWidth((float)$fields['WIDTH'])
            ->setLength((float)$fields['LENGTH'])
            ->setProductIblockId((string)$fields['ELEMENT_IBLOCK_ID'])
            ->setProductXmlId($fields['ELEMENT_XML_ID'])
            ->setProductName($fields['ELEMENT_NAME']);
    }

    /**
     * @param string $primary
     *
     * @throws CatalogProductNotFoundException
     * @return CatalogProduct
     */
    public static function createFromPrimary(string $primary): CatalogProduct
    {
        $fields = (new CatalogProductQuery())
            ->withFilter(['ID' => $primary])
            ->exec()
            ->current();
        if ($fields) {
            return new static($fields);
        }

        throw new CatalogProductNotFoundException(sprintf('Product with identifier %s not found', $primary));
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return CatalogProduct
     */
    public function setId(int $id): CatalogProduct
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     * @return CatalogProduct
     */
    public function setWeight(float $weight): CatalogProduct
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return float
     */
    public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * @param float $height
     * @return CatalogProduct
     */
    public function setHeight(float $height): CatalogProduct
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return float
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * @param float $width
     * @return CatalogProduct
     */
    public function setWidth(float $width): CatalogProduct
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return float
     */
    public function getLength(): float
    {
        return $this->length;
    }

    /**
     * @param float $length
     * @return CatalogProduct
     */
    public function setLength(float $length): CatalogProduct
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return int
     */
    public function getProductIblockId(): int
    {
        return $this->productIblockId;
    }

    /**
     * @param int $productIblockId
     * @return CatalogProduct
     */
    public function setProductIblockId(int $productIblockId): CatalogProduct
    {
        $this->productIblockId = $productIblockId;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductXmlId(): string
    {
        return $this->productXmlId;
    }

    /**
     * @param string $productXmlId
     * @return CatalogProduct
     */
    public function setProductXmlId(string $productXmlId): CatalogProduct
    {
        $this->productXmlId = $productXmlId;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductName(): string
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     * @return CatalogProduct
     */
    public function setProductName(string $productName): CatalogProduct
    {
        $this->productName = $productName;
        return $this;
    }
}
