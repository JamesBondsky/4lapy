<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\CategoriesRequest;
use FourPaws\MobileApiBundle\Dto\Response\CategoriesResponse;

class CategoryController extends FOSRestController
{
    /**
     * @Rest\Get(path="/categories")
     * @see CategoriesRequest
     * @see CategoriesResponse
     */
    public function getAction()
    {
        /**
         * @todo кеширование
         */
    }
}
