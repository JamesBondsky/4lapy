<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Exception\CategoryNotFoundException;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\MobileApiBundle\Dto\Object\CatalogCategory;
use FourPaws\MobileApiBundle\Dto\Request\CategoryRequest;
use WebArch\BitrixCache\BitrixCache;

class CategoryService
{
    /**
     * @param CategoryRequest $categoryRequest
     *
     * @throws CategoryNotFoundException
     * @return CatalogCategory[]|Collection
     */
    public function get(CategoryRequest $categoryRequest): Collection
    {
        $catalogCategories = $this->getCatalogCategory($categoryRequest->getId() ?: false);
        if (0 === $catalogCategories->count()) {
            throw new CategoryNotFoundException(sprintf('Category %s not found', $categoryRequest->getId()));
        }
        return $this->getCatalogCategory($categoryRequest->getId() ?: false);
    }

    protected function getCatalogCategory($parentId): Collection
    {
        // Cache code
        $categoryCallback = function () use ($parentId) {
            return (new CategoryQuery())
                ->withFilterParameter('SECTION_ID', $parentId)
                ->exec()
                ->map(function (Category $category) {
                    return $this->toApiFormat($category);
                });
        };

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return (new BitrixCache())
            ->withId(__METHOD__ . $parentId)
            ->withIblockTag(IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS))
            ->resultOf($categoryCallback)['result'];
    }

    /**
     * Convert Category to CatalogCategory
     *
     * @param Category $categoryItem
     *
     * @throws FileNotFoundException
     * @return CatalogCategory
     */
    private function toApiFormat(Category $categoryItem): CatalogCategory
    {
        $category = new CatalogCategory();
        $category->setId($categoryItem->getId());
        $category->setTitle($categoryItem->getCanonicalName());

        if ($categoryItem->getPictureId()) {
            $category->setPicture($this->getPicturePath($categoryItem->getPictureId()));
        }

        if ($categoryItem->getChild()->count()) {
            $category->setHasChild(true);
            $category->setChild($this->createTree($categoryItem));
        }

        return $category;
    }

    /**
     * @param int $id
     * @return string
     */
    private function getPicturePath(int $id)
    {
        try {
            /** @var \FourPaws\BitrixOrm\Model\File $imageDecorator */
            $imageDecorator = ResizeImageDecorator::createFromPrimary($id)
                ->setResizeWidth(200)
                ->setResizeHeight(200);
            $picture = new FullHrefDecorator($imageDecorator->getSrc());
        } catch (FileNotFoundException $e) {
            $picture = '';
        }
        return $picture;
    }

    /**
     * Create recursive tree childs
     *
     * @param Category $categoryItem
     *
     * @throws FileNotFoundException
     * @return array
     */
    private function createTree(Category $categoryItem): array
    {
        $children = [
            (new CatalogCategory())
                ->setTitle('Все')
                ->setId($categoryItem->getId())
                ->setHasChild(false)
                ->setPicture($this->getPicturePath($categoryItem->getPictureId()))
        ];
        foreach ($categoryItem->getChild() as $child) {
            $children[] = $this->toApiFormat($child);
        }

        return $children;
    }
}
