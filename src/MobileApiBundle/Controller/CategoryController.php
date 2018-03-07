<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\CategoryRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\CategoriesResponse;
use FourPaws\MobileApiBundle\Services\Api\CategoryService;

class CategoryController extends FOSRestController
{
    /**
     * @var CategoryService
     */
    private $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
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
        return new Response(new CategoriesResponse($this->categoryService->get($categoryRequest)));
    }
}
