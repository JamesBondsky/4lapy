<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\FoodSelectionBundle\AjaxController;

use Bitrix\Main\ArgumentException;
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
    ) {
        $this->foodSelectionService = $foodSelectionService;
    }

    /**
     * Тип питомца - начало
     * @Route("/step/begin/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function beginAction(Request $request): JsonResponse
    {
        /** оставляем токо одно значение, ибо все остальное надо сбросить */
        $values = ['pet_type' => $request->get('pet_type')];

        $petTypeCode = '';
        if (!empty($values['pet_type'])) {
            /** нужно для определения ттого показывать или нет доп. поле собак */
            $petTypeCode = current($this->foodSelectionService->getSections(
                [
                    'filter' => ['ID' => $values['pet_type']],
                    'select' => ['CODE'],
                ]
            ))->getCode();
        }


        $sections = [
            'pet_type',
            'pet_age',
            'pet_size',
            'food_spec',
            'food_consistence',
        ];
        $sections = $this->foodSelectionService->getSectionsByXmlId($sections, 1);
        /** @var IblockSect[] $sect */
        $sect = $this->foodSelectionService->getSections([
            '=SECTION_ID' => array_keys($sections),
        ]);
        if (!empty($sect)) {
            /** @var IblockSect $sectItem */
            foreach ($sect as $sectItem) {
                $parentId = $sectItem->getIblockSectionId();
                if(isset($sections[$parentId])) {
                    $xmlId = $sections[$parentId]->getXmlId();
                    if (!isset($all_sections[$xmlId])) {
                        $all_sections[$xmlId] = [
                            'ITEMS'     => [],
                            'SECT_NAME' => $sections[$parentId]->getName(),
                        ];
                    }
                    $all_sections[$xmlId]['ITEMS'][] = $sectItem;
                }
            }
        }

        $full_fields = false;
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot()
            . '/local/components/fourpaws/catalog.food.selection/templates/.default/include/fields.php';

        return JsonSuccessResponse::createWithData(
            'Успешный аякс',
            [
                'form_html'  => ob_get_clean(),
                'items_html' => '',
                'has_items'  => false,
            ]
        );
    }

    /**
     * аякс после заполнения всех обязательных полей
     * @Route("/step/required/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function requiredAction(Request $request): JsonResponse
    {
        $hasItems = false;
        $values = $request->query->all();
        \TrimArr($values);
        foreach ($values as $key => $val) {
            if ((int)$val <= 0) {
                unset($values[$key]);
            }
        }

        if (!empty($values['pet_type'])) {
            $petTypeCode = current($this->foodSelectionService->getSections(
                [
                    'filter' => ['ID' => $values['pet_type']],
                    'select' => ['CODE'],
                ]
            ))->getCode();
        }

        $sections = [];
        $sections = $this->foodSelectionService->getSectionsByXmlId($sections, 1);
        /** @var IblockSect[] $sect */
        $sect = $this->foodSelectionService->getSections([
            '=SECTION_ID' => array_keys($sections),
        ]);
        if (!empty($sect)) {
            /** @var IblockSect $sectItem */
            foreach ($sect as $sectItem) {
                $parentId = $sectItem->getIblockSectionId();
                if(isset($sections[$parentId])) {
                    $xmlId = $sections[$parentId]->getXmlId();
                    if (!isset($all_sections[$xmlId])) {
                        $all_sections[$xmlId] = [
                            'ITEMS'     => [],
                            'SECT_NAME' => $sections[$parentId]->getName(),
                        ];
                    }
                    $all_sections[$xmlId]['ITEMS'][] = $sectItem;
                }
            }
        }

        /** подгружаем так же необязательные поля */
        $full_fields = true;
        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot()
            . '/local/components/fourpaws/catalog.food.selection/templates/.default/include/fields.php';
        $form_html = ob_get_clean();

        try {
            $recommendedItems = $this->foodSelectionService->getProductsBySections(array_values($values), [], 6);
        } catch (ArgumentException|SystemException $e) {
            $recommendedItems = [];
        }

        /** @var Product $product */
        $exceptionItems = [];
        if (\is_array($recommendedItems) && !empty($recommendedItems)) {
            $hasItems = true;
            foreach ($recommendedItems as $product) {
                $exceptionItems[] = $product->getId();
            }
        }
        $excludeSections = [$values['food_consistence']];
        unset($values['food_consistence']);
        try {
            /** дополнительные итемы */
            $limit = 3;
            $alsoItems = $this->foodSelectionService->getProductsBySections(array_values($values), $exceptionItems, $limit, $excludeSections);
            /** быстрый фикс на исключение итемов из раздела который был выбран, выбираем все */
//            $i=0;
//            $alsoItemsOld = $alsoItems;
//            $alsoItems = [];
//            foreach ($alsoItemsOld as $alsoItem) {
//                if($i === $limit){
//                    break;
//                }
//                if(!\in_array($excludeSections, $alsoItem->getSectionsIdList(), true)){
//                    $alsoItems[] = $alsoItem;
//                    $i++;
//                }
//            }
        } catch (ArgumentException|SystemException $e) {
            $alsoItems = [];
        }

        if (\is_array($alsoItems) && !empty($alsoItems) && !$hasItems) {
            $hasItems = true;
        }

        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot()
            . '/local/components/fourpaws/catalog.food.selection/templates/.default/include/items.php';
        $items_html = ob_get_clean();

        return JsonSuccessResponse::createWithData(
            'Успешный аякс',
            [
                'form_html'  => $form_html,
                'items_html' => $items_html,
                'has_items'  => $hasItems,
            ]
        );
    }

    /**
     * аякс при выборе значения неоязательного поля
     * @Route("/step/not_required/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function notRequiredAction(Request $request): JsonResponse
    {
        $hasItems = false;
        $values = $request->query->all();
        \TrimArr($values);
        foreach ($values as $key => $val) {
            if ((int)$val <= 0) {
                unset($values[$key]);
            }
        }

        try {
            $recommendedItems = $this->foodSelectionService->getProductsBySections(array_values($values), [], 6);
        } catch (ArgumentException|SystemException $e) {
            $recommendedItems = [];
        }

        /** @var Product $product */
        $exceptionItems = [];
        if (\is_array($recommendedItems) && !empty($recommendedItems)) {
            $hasItems = true;
            foreach ($recommendedItems as $product) {
                $exceptionItems[] = $product->getId();
            }
        }
        unset($values['food_consistence']);
        try {
            $alsoItems = $this->foodSelectionService->getProductsBySections(array_values($values), $exceptionItems, 3);
        } catch (ArgumentException|SystemException $e) {
            $alsoItems = [];
        }

        if (\is_array($alsoItems) && !empty($alsoItems) && !$hasItems) {
            $hasItems = true;
        }

        ob_start();
        /** @noinspection PhpIncludeInspection */
        include_once App::getDocumentRoot()
            . '/local/components/fourpaws/catalog.food.selection/templates/.default/include/items.php';
        $items_html = ob_get_clean();

        return JsonSuccessResponse::createWithData(
            'Успешный аякс',
            [
                'items_html' => $items_html,
                'has_items'  => $hasItems,
            ]
        );
    }
}
