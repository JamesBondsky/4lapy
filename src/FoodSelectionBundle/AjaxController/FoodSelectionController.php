<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\FoodSelectionBundle\AjaxController;

use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\BitrixOrm\Model\IblockSect;
use FourPaws\Catalog\Model\Product;
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
     * Тип питомца
     * @Route("/step/pet/type/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function showStepPetTypeAction(Request $request) : JsonResponse
    {
        $_SESSION['SELECT_NUMBER'] = $_SESSION['RADIO_NUMBER'] = -1;
        $petType                   = $request->query->get('pet_type');
        /** @var IblockSect $sect */
        $petTypeSections      = $this->foodSelectionService->getSections(
            [
                'filter' => ['ID' => $petType],
                'select' => ['CODE'],
            ]
        );
        $sect                 = current($petTypeSections);
        $_SESSION['PET_TYPE'] = $sect->getCode();
        $step                 = 'pet_age';
        /** @noinspection PhpUnusedLocalVariableInspection */
        $sections =
            $this->foodSelectionService->getSectionsByParentSectionId(
                $this->foodSelectionService->getSectionIdByXmlId($step, 1)
            );
        $nextStep = 2;
        $nextUrl  = '/ajax/food_selection/show/step/pet/age/';
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot()
                     . '/local/components/fourpaws/catalog.food.selection/templates/.default/include/' . $step . '.php';
        $html = ob_get_clean();
        
        return JsonSuccessResponse::createWithData(
            'Успешный аякс',
            [
                'showItems' => false,
                'html'      => $html,
                'food'      => [
                    'next_step_url' => $nextUrl,
                    'num'           => $nextStep,
                ],
            ]
        );
    }
    
    /**
     * Возраст питомца
     * @Route("/step/pet/age/", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function showStepPetAgeAction() : JsonResponse
    {
        if ($_SESSION['PET_TYPE'] === 'dog') {
            $step     = 'pet_size';
            $nextStep = 3;
            $nextUrl  = '/ajax/food_selection/show/step/pet/size/';
        } else {
            $step     = 'food_spec';
            $nextStep = 4;
            $nextUrl  = '/ajax/food_selection/show/step/food/specialize/';
        }
        unset($_SESSION['PET_TYPE']);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $sections =
            $this->foodSelectionService->getSectionsByParentSectionId(
                $this->foodSelectionService->getSectionIdByXmlId($step, 1)
            );
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot()
                     . '/local/components/fourpaws/catalog.food.selection/templates/.default/include/' . $step . '.php';
        $html = ob_get_clean();
        
        return JsonSuccessResponse::createWithData(
            'Успешный аякс',
            [
                'html' => $html,
                'food' => [
                    'next_step_url' => $nextUrl,
                    'num'           => $nextStep,
                ],
            ]
        );
    }
    
    /**
     * Размер питомца
     * @Route("/step/pet/size/", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function showStepPetSizeAction() : JsonResponse
    {
        $step = 'food_spec';
        /** @noinspection PhpUnusedLocalVariableInspection */
        $sections =
            $this->foodSelectionService->getSectionsByParentSectionId(
                $this->foodSelectionService->getSectionIdByXmlId($step, 1)
            );
        $nextStep = 4;
        $nextUrl  = '/ajax/food_selection/show/step/food/specialize/';
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot()
                     . '/local/components/fourpaws/catalog.food.selection/templates/.default/include/' . $step . '.php';
        $html = ob_get_clean();
        
        return JsonSuccessResponse::createWithData(
            'Успешный аякс',
            [
                'html' => $html,
                'food' => [
                    'next_step_url' => $nextUrl,
                    'num'           => $nextStep,
                ],
            ]
        );
    }
    
    /**
     * Специализация корма
     * @Route("/step/food/specialize/", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function showStepFoodSpecializeAction() : JsonResponse
    {
        $step = 'food_ingridient';
        /** @noinspection PhpUnusedLocalVariableInspection */
        $sections =
            $this->foodSelectionService->getSectionsByParentSectionId(
                $this->foodSelectionService->getSectionIdByXmlId($step, 1)
            );
        $nextStep = 5;
        $nextUrl  = '/ajax/food_selection/show/step/food/features/';
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot()
                     . '/local/components/fourpaws/catalog.food.selection/templates/.default/include/' . $step . '.php';
        $html = ob_get_clean();
        
        return JsonSuccessResponse::createWithData(
            'Успешный аякс',
            [
                'html' => $html,
                'food' => [
                    'next_step_url' => $nextUrl,
                    'num'           => $nextStep,
                ],
            ]
        );
    }
    
    /**
     * Особенности корма
     * @Route("/step/food/features/", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function showStepFoodFeaturesAction() : JsonResponse
    {
        $step = 'food_consistence';
        /** @noinspection PhpUnusedLocalVariableInspection */
        $sections =
            $this->foodSelectionService->getSectionsByParentSectionId(
                $this->foodSelectionService->getSectionIdByXmlId($step, 1)
            );
        $nextStep = 6;
        $nextUrl  = '/ajax/food_selection/show/step/food/type/';
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot()
                     . '/local/components/fourpaws/catalog.food.selection/templates/.default/include/' . $step . '.php';
        $html = ob_get_clean();
        
        return JsonSuccessResponse::createWithData(
            'Успешный аякс',
            [
                'html' => $html,
                'food' => [
                    'next_step_url' => $nextUrl,
                    'num'           => $nextStep,
                ],
            ]
        );
    }
    
    /**
     * Тип корма
     * @Route("/step/food/type/", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function showStepFoodTypeAction() : JsonResponse
    {
        $step = 'food_flavour';
        /** @noinspection PhpUnusedLocalVariableInspection */
        $sections =
            $this->foodSelectionService->getSectionsByParentSectionId(
                $this->foodSelectionService->getSectionIdByXmlId($step, 1)
            );
        $nextStep = 7;
        $nextUrl  = '/ajax/food_selection/show/items/';
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot()
                     . '/local/components/fourpaws/catalog.food.selection/templates/.default/include/' . $step . '.php';
        $html = ob_get_clean();
        
        return JsonSuccessResponse::createWithData(
            'Успешный аякс',
            [
                'html' => $html,
                'food' => [
                    'next_step_url' => $nextUrl,
                    'num'           => $nextStep,
                ],
            ]
        );
    }
    
    /**
     * Вкус корма - показ элементов
     * @Route("/items/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws SystemException
     */
    public function showItemsAction(Request $request) : JsonResponse
    {
        $data = $request->query->getIterator()->getArrayCopy();
        \TrimArr($data);
        foreach ($data as $key => $val) {
            if((int)$val <= 0){
                unset($data[$key]);
            }
        }
    
        $step = 'items';
        /** @noinspection PhpUnusedLocalVariableInspection */
        $recommendedItems = $this->foodSelectionService->getProductsBySections(array_values($data));

        /** @var Product $product */
        $exceptionItems = [];
        if(\is_array($recommendedItems) && !empty($recommendedItems)) {
            foreach ($recommendedItems as $product) {
                $exceptionItems[] = $product->getId();
            }
        }
        unset($data['food_consistence']);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $alsoItems = $this->foodSelectionService->getProductsBySections(array_values($data), $exceptionItems);
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot()
                     . '/local/components/fourpaws/catalog.food.selection/templates/.default/include/' . $step . '.php';
        $html = ob_get_clean();
        
        return JsonSuccessResponse::createWithData(
            'Успешный аякс',
            [
                'html'      => $html,
                'showItems' => true,
                'food'      => [
                    'next_step_url' => '',
                    'num'           => '',
                ],
            ]
        );
    }
}
