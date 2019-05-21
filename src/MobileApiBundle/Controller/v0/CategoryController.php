<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\CategoryRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\CategoriesResponse;
use FourPaws\MobileApiBundle\Services\Api\CategoryService as ApiCategoryService;

class CategoryController extends FOSRestController
{
    /**
     * @var ApiCategoryService
     */
    private $apiCategoryService;

    public function __construct(ApiCategoryService $apiCategoryService)
    {
        $this->apiCategoryService = $apiCategoryService;
    }

    /**
     * @Rest\Get(path="/categories/")
     * @Rest\View()
     * @param CategoryRequest $categoryRequest
     *
     * @throws \FourPaws\Catalog\Exception\CategoryNotFoundException
     * @return Response
     */
    public function getCategoryAction(CategoryRequest $categoryRequest): Response
    {
        return new Response(new CategoriesResponse($this->apiCategoryService->get($categoryRequest)));
    }
}
