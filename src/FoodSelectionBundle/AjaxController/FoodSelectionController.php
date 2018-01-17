<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\FoodSelectionBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\FoodSelectionBundle\Service\FoodSelectionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AddressController
 *
 * @package FourPaws\PersonalBundle\AjaxController
 * @Route("/show")
 */
class FoodSelectionController extends Controller
{
    /**
     * @var FoodSelectionService
     */
    private $foodSelectionService;
    
    public function __construct(
        FoodSelectionService $foodSelectionService
    ) {
        $this->foodSelectionService = $foodSelectionService;
    }
    
    /**
     * @Route("/step/pet/type/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function showStepPetTypeAction(Request $request) : JsonResponse
    {
        $data = $request->request->getIterator()->getArrayCopy();
        
        if(1 === 2) {
            return JsonSuccessResponse::create(
                '',
                200,
                [],
                ['reload' => true]
            );
        }
        
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
    
    /**
     * @Route("/step/pet/age/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function showStepPetAgeAction(Request $request) : JsonResponse
    {
        $data = $request->request->getIterator()->getArrayCopy();
        
        if(1 === 2) {
            return JsonSuccessResponse::create(
                '',
                200,
                [],
                ['reload' => true]
            );
        }
        
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
    
    /**
     * @Route("/step/pet/size/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function showStepPetSizeAction(Request $request) : JsonResponse
    {
        $data = $request->request->getIterator()->getArrayCopy();
        
        if(1 === 2) {
            return JsonSuccessResponse::create(
                '',
                200,
                [],
                ['reload' => true]
            );
        }
        
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
    
    /**
     * @Route("/step/food/specialize/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function showStepFoodSpecializeAction(Request $request) : JsonResponse
    {
        $data = $request->request->getIterator()->getArrayCopy();
        
        if(1 === 2) {
            return JsonSuccessResponse::create(
                '',
                200,
                [],
                ['reload' => true]
            );
        }
        
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
    
    /**
     * @Route("/step/food/features/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function showStepFoodFeaturesAction(Request $request) : JsonResponse
    {
        $data = $request->request->getIterator()->getArrayCopy();
        
        if(1 === 2) {
            return JsonSuccessResponse::create(
                '',
                200,
                [],
                ['reload' => true]
            );
        }
        
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
    
    /**
     * @Route("/step/food/type/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function showStepFoodTypeAction(Request $request) : JsonResponse
    {
        $data = $request->request->getIterator()->getArrayCopy();
        
        if(1 === 2) {
            return JsonSuccessResponse::create(
                '',
                200,
                [],
                ['reload' => true]
            );
        }
        
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
    
    /**
     * @Route("/items/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function showItemsAction(Request $request) : JsonResponse
    {
        $data = $request->request->getIterator()->getArrayCopy();
        
        if(1 === 2) {
            return JsonSuccessResponse::create(
                '',
                200,
                [],
                ['reload' => true]
            );
        }
        
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
}
