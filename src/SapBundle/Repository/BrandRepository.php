<?php

namespace FourPaws\SapBundle\Repository;

use Cocur\Slugify\SlugifyInterface;
use FourPaws\Catalog\Model\Brand;
use FourPaws\Catalog\Query\BrandQuery;

class BrandRepository
{
    /**
     * @var SlugifyInterface
     */
    private $slugify;

    /**
     * @var \CIBlockElement
     */
    private $iblockElement;

    public function __construct(SlugifyInterface $slugify)
    {
        $this->slugify = $slugify;
        $this->iblockElement = new \CIBlockElement();
    }

    /**
     * @param string $xmlId
     *
     * @return null|Brand
     */
    public function findByXmlId(string $xmlId)
    {
        $query = new BrandQuery();
        $query->withFilter([
            '=XML_ID' => $xmlId,
        ]);
        return $query->exec()->current();
    }

    /**
     * @param string $code
     *
     * @return null|Brand
     */
    public function findByCode(string $code)
    {
        $query = new BrandQuery();
        $query->withFilter([
            '=CODE' => $code,
        ]);
        return $query->exec()->current();
    }

    /**
     *
     * @param string $xmlId
     * @param string $name
     *
     * @return null|Brand
     */
    public function getOrCreate(string $xmlId, string $name)
    {
        return $this->findByXmlId($xmlId) ?: $this->create($xmlId, $name);
    }

    /**
     *
     * @param string $xmlId
     * @param string $name
     *
     * @return null|Brand
     */
    public function create(string $xmlId, string $name)
    {
        $brand = new Brand();
        $brand
            ->withName($name)
            ->withXmlId($xmlId)
            ->withCode($this->slugify->slugify($name));
        if ($id = $this->iblockElement->Add($brand->toArray())) {
            $brand->withId($id);
        }

        return $id > 0 ? $brand : null;
    }
}
