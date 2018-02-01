<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Model\Category;
use FourPaws\Catalog\Query\CategoryQuery;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Object\CatalogCategory;
use FourPaws\MobileApiBundle\Dto\Request\CategoriesRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\CategoriesResponse;
use WebArch\BitrixCache\BitrixCache;

class CategoryController extends FOSRestController
{
    /**
     * @Rest\Get(path="/categories")
     * @see CategoriesRequest
     * @see CategoriesResponse
     * @throws \FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException
     */
    public function getCategoryAction()
    {
        // Demo data
        $parentId = null ?: false;

        // Cache code
        $getCategoryCode = function () use ($parentId) {
            $response = new Response();

            // Get data
            $categoryCollection = (new CategoryQuery())->withFilterParameter('SECTION_ID', $parentId)->exec();

            if ($categoryCollection->count()) {
                $categories = new CategoriesResponse();
                foreach ($categoryCollection as $categoryItem) {
                    $category = $this->toApiFormat($categoryItem);
                    $categories->addCategory($category);
                }

                $response->setData($categories);
            } else {
                $response->addError(new Error(100, 'Нет категорий с заданным родителем'));
            }

            return $response;
        };

        // Use cache
        $response = (new BitrixCache())
            ->withId(__METHOD__ . $parentId)
            ->resultOf($getCategoryCode);

        // Set view
        return $this->view($response);
    }

    /**
     * Convert Category to CatalogCategory
     *
     * @param Category $categoryItem
     *
     * @return CatalogCategory
     * @throws \FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException
     */
    private function toApiFormat(Category $categoryItem): CatalogCategory
    {
        $category = new CatalogCategory();
        $category->setTitle($categoryItem->getCanonicalName());

        if ($categoryItem->getPictureId()) {
            $picture =
                ResizeImageDecorator::createFromPrimary($categoryItem->getPictureId())
                    ->setResizeWidth(200)
                    ->setResizeHeight(200);
            $category->setPicture(new FullHrefDecorator($picture->getSrc()));
        }

        if ($categoryItem->getChild()->count()) {
            $category->setHasChild(true);
            $category->setChild($this->createTree($categoryItem));
        }

        return $category;
    }

    /**
     * Create recursive tree childs
     *
     * @param Category $categoryItem
     *
     * @return array
     * @throws \FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException
     */
    private function createTree(Category $categoryItem): array
    {
        $childs = [];
        foreach ($categoryItem->getChild(false) as $child) {
            $childs[] = $this->toApiFormat($child);
        }

        return $childs;
    }
}
