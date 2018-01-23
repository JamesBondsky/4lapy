<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\FoodSelectionBundle\AjaxController;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use FourPaws\App\Application as App;
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
    )
    {
        $this->foodSelectionService = $foodSelectionService;
    }
    
    /**
     * @Route("/step/pet/type/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws IblockNotFoundException
     */
    public function showStepPetTypeAction(Request $request) : JsonResponse
    {
        $petType = $request->get('pet_type');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $step     = 'pet_age';
        $sections = $this->foodSelectionService->getSectionsByXmlIdAndParentSection($step, $petType, 2);
        $nextStep = 2;
        $nextUrl  = '/ajax/food_selection/show/step/pet/age/';
        ob_start();
        require_once App::getDocumentRoot()
                     . '/common/local/components/fourpaws/catalog.food.selection/templates/.default/include/' . $step
                     . '.php';
        $html = ob_get_clean();
        
        return JsonSuccessResponse::create(
            '',
            200,
            [
                'html' => $html,
                'food' => [
                    'next_step_url' => $nextUrl,
                    'num'           => $nextStep,
                ],
            ],
            ['reload' => true]
        );
    }
    
    /**
     * @Route("/step/pet/age/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws IblockNotFoundException
     */
    public function showStepPetAgeAction(Request $request) : JsonResponse
    {
        $petType = $request->get('pet_type');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $step     = 'pet_size';
        $sections = $this->foodSelectionService->getSectionsByXmlIdAndParentSection($step, $petType, 2);
        $nextStep = 3;
        $nextUrl  = '/ajax/food_selection/show/step/pet/size/';
        ob_start();
        require_once App::getDocumentRoot()
                     . '/common/local/components/fourpaws/catalog.food.selection/templates/.default/include/' . $step
                     . '.php';
        $html = ob_get_clean();
    
        return JsonSuccessResponse::create(
            '',
            200,
            [
                'html' => $html,
                'food' => [
                    'next_step_url' => $nextUrl,
                    'num'           => $nextStep,
                ],
            ],
            ['reload' => true]
        );
    }
    
    /**
     * @Route("/step/pet/size/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws IblockNotFoundException
     */
    public function showStepPetSizeAction(Request $request) : JsonResponse
    {
        $petType = $request->get('pet_type');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $step     = 'food_purpose';
        $sections = $this->foodSelectionService->getSectionsByXmlIdAndParentSection($step, $petType, 2);
        $nextStep = 4;
        $nextUrl  = '/ajax/food_selection/show/step/food/specialize/';
        ob_start();
        require_once App::getDocumentRoot()
                     . '/common/local/components/fourpaws/catalog.food.selection/templates/.default/include/' . $step
                     . '.php';
        $html = ob_get_clean();
    
        return JsonSuccessResponse::create(
            '',
            200,
            [
                'html' => $html,
                'food' => [
                    'next_step_url' => $nextUrl,
                    'num'           => $nextStep,
                ],
            ],
            ['reload' => true]
        );
    }
    
    /**
     * @Route("/step/food/specialize/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws IblockNotFoundException
     */
    public function showStepFoodSpecializeAction(Request $request) : JsonResponse
    {
        $petType = $request->get('pet_type');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $step     = 'pet_age';
        $sections = $this->foodSelectionService->getSectionsByXmlIdAndParentSection($step, $petType, 2);
        $nextStep = 5;
        $nextUrl  = '/ajax/food_selection/show/step/pet/age/';
        ob_start();
        require_once App::getDocumentRoot()
                     . '/common/local/components/fourpaws/catalog.food.selection/templates/.default/include/' . $step
                     . '.php';
        $html = ob_get_clean();
    
        return JsonSuccessResponse::create(
            '',
            200,
            [
                'html' => $html,
                'food' => [
                    'next_step_url' => $nextUrl,
                    'num'           => $nextStep,
                ],
            ],
            ['reload' => true]
        );
    }
    
    /**
     * @Route("/step/food/features/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws IblockNotFoundException
     */
    public function showStepFoodFeaturesAction(Request $request) : JsonResponse
    {
        $petType = $request->get('pet_type');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $step     = 'pet_age';
        $sections = $this->foodSelectionService->getSectionsByXmlIdAndParentSection($step, $petType, 2);
        $nextStep = 6;
        $nextUrl  = '/ajax/food_selection/show/step/pet/age/';
        ob_start();
        require_once App::getDocumentRoot()
                     . '/common/local/components/fourpaws/catalog.food.selection/templates/.default/include/' . $step
                     . '.php';
        $html = ob_get_clean();
    
        return JsonSuccessResponse::create(
            '',
            200,
            [
                'html' => $html,
                'food' => [
                    'next_step_url' => $nextUrl,
                    'num'           => $nextStep,
                ],
            ],
            ['reload' => true]
        );
    }
    
    /**
     * @Route("/step/food/type/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws IblockNotFoundException
     */
    public function showStepFoodTypeAction(Request $request) : JsonResponse
    {
        $petType = $request->get('pet_type');
        /** @noinspection PhpUnusedLocalVariableInspection */
        $step     = 'pet_age';
        $sections = $this->foodSelectionService->getSectionsByXmlIdAndParentSection($step, $petType, 2);
        $nextStep = 7;
        $nextUrl  = '/ajax/food_selection/show/items/';
        ob_start();
        require_once App::getDocumentRoot()
                     . '/common/local/components/fourpaws/catalog.food.selection/templates/.default/include/' . $step
                     . '.php';
        $html = ob_get_clean();
    
        return JsonSuccessResponse::create(
            '',
            200,
            [
                'html' => $html,
                'food' => [
                    'next_step_url' => $nextUrl,
                    'num'           => $nextStep,
                ],
            ],
            ['reload' => true]
        );
    }
    
    /**
     * @Route("/items/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws IblockNotFoundException
     */
    public function showItemsAction(Request $request) : JsonResponse
    {
        $data = $request->request->getIterator()->getArrayCopy();
        /** @noinspection PhpUnusedLocalVariableInspection */
        $step     = 'items';
        $items = $this->foodSelectionService->getItemsBySections(array_values($data));
        ob_start();
        require_once App::getDocumentRoot()
                     . '/common/local/components/fourpaws/catalog.food.selection/templates/.default/include/' . $step
                     . '.php';
        $html = ob_get_clean();
    
        return JsonSuccessResponse::create(
            '',
            200,
            [
                'html' => $html,
                'showItems' => true
            ],
            ['reload' => true]
        );
    }
}
