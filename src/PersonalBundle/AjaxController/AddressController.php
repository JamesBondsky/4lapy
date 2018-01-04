<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\AjaxController;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AddressController
 *
 * @package FourPaws\UserBundle\Controller
 * @Route("/address")
 */
class AddressController extends Controller
{
    /**
     * @Route("/add/", methods={"POST"})
     * @param Request $request
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return JsonResponse
     */
    public function addAction(Request $request) : JsonResponse
    {
        $addressService = App::getInstance()->getContainer()->get('address.service');
        $data           = $request->request->getIterator()->getArrayCopy();
        if (empty($data)) {
            return JsonErrorResponse::createWithData(
                'Не указаны данные для добавления',
                ['errors' => ['emptyData' => 'Не указаны данные для добавления']]
            );
        }
        try {
            if ($addressService->add($data)) {
                return JsonSuccessResponse::create(
                    '',
                    200,
                    [],
                    ['reload' => true]
                );
            }
        } catch (BitrixRuntimeException $e) {
        } catch (\Exception $e) {
        }
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
    
    /**
     * @Route("/update/", methods={"POST"})
     * @param Request $request
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return JsonResponse
     */
    public function updateAction(Request $request) : JsonResponse
    {
        $addressService = App::getInstance()->getContainer()->get('address.service');
        $data           = $request->request->getIterator()->getArrayCopy();
        if (empty($data)) {
            return JsonErrorResponse::createWithData(
                'Не указаны данные для обновления',
                ['errors' => ['emptyData' => 'Не указаны данные для обновления']]
            );
        }
        if ((int)$data['ID'] < 1) {
            return JsonErrorResponse::createWithData(
                'Не указан элемент для обновления',
                ['errors' => ['emptyIdError' => 'Не указан элемент для обновления']]
            );
        }
        try {
            if ($addressService->update($data)) {
                return JsonSuccessResponse::create(
                    '',
                    200,
                    [],
                    ['reload' => true]
                );
            }
        } catch (BitrixRuntimeException $e) {
        } catch (ConstraintDefinitionException $e) {
        } catch (\Exception $e) {
        }
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
    
    /**
     * @Route("/delete/", methods={"GET"})
     * @param Request $request
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return JsonResponse
     */
    public function deleteAction(Request $request) : JsonResponse
    {
        $addressService = App::getInstance()->getContainer()->get('address.service');
        $delId          = (int)$request->get('id');
        if ($delId < 1) {
            return JsonErrorResponse::createWithData(
                'Не указан элемент для удаления',
                ['errors' => ['emptyIdError' => 'Не указан элемент для удаления']]
            );
        }
        try {
            if ($addressService->delete($delId)) {
                return JsonSuccessResponse::create(
                    '',
                    200,
                    [],
                    ['reload' => true]
                );
            }
        } catch (BitrixRuntimeException $e) {
        } catch (ConstraintDefinitionException $e) {
        } catch (\Exception $e) {
        }
        
        return JsonErrorResponse::createWithData(
            'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Непредвиденная ошибка. Пожалуйста, обратитесь к администратору сайта']]
        );
    }
}
