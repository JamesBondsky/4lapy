<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\AjaxController;

use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\PersonalBundle\Service\ReferralService;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ReferralController
 *
 * @package FourPaws\PersonalBundle\AjaxController
 * @Route("/referral")
 */
class ReferralController extends Controller
{
    /**
     * @var ReferralService
     */
    private $referralService;
    
    public function __construct(
        ReferralService $referralService
    ) {
        $this->referralService = $referralService;
    }
    
    /**
     * @Route("/add/", methods={"POST"})
     * @param Request $request
     *
     * @throws ServiceNotFoundException
     * @return JsonResponse
     */
    public function addAction(Request $request) : JsonResponse
    {
        $data = $request->request->getIterator()->getArrayCopy();
        if (empty($data)) {
            return JsonErrorResponse::createWithData(
                'Не указаны данные для добавления',
                ['errors' => ['emptyData' => 'Не указаны данные для добавления']]
            );
        }
        try {
            if ($this->referralService->add($data)) {
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
     * @Route("/get_user_info/", methods={"POST"})
     * @param Request $request
     *
     * @throws ServiceNotFoundException
     * @return JsonResponse
     */
    public function getUserInfoAction(Request $request) : JsonResponse
    {
        $card = $request->get('card');
        if (empty($card)) {
            return JsonErrorResponse::createWithData(
                'Не указан код карты',
                ['errors' => ['emptyData' => 'Не указан код карты']]
            );
        }
        try {
            if ($this->referralService->findBy($card)) {
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
