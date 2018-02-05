<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\AjaxController;

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\PersonalBundle\Service\BonusService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BonusController
 *
 * @package FourPaws\PersonalBundle\AjaxController
 * @Route("/bonus")
 */
class BonusController extends Controller
{
    /**
     * @var BonusService
     */
    private $bonusService;
    
    public function __construct(
        BonusService $bonusService
    )
    {
        $this->bonusService = $bonusService;
    }
    
    /**
     * @Route("/card/link/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function addAction(Request $request) : JsonResponse
    {
        $card = $request->get('card', '');
        if (empty($card)) {
            return JsonErrorResponse::createWithData(
                'Не указан номер карты',
                ['errors' => ['emptyData' => 'Не указан номер карты']]
            );
        }
        
        if ($this->bonusService->activateBonusCard($card)) {
            return JsonSuccessResponse::create(
                'Карта привязана',
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
