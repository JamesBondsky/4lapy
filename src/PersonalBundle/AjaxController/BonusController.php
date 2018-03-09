<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\AjaxController;

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\PersonalBundle\Exception\CardNotValidException;
use FourPaws\PersonalBundle\Service\BonusService;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
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
    /** @var AjaxMess  */
    private $ajaxMess;

    public function __construct(
        BonusService $bonusService,
        AjaxMess $ajaxMess
    )
    {
        $this->bonusService = $bonusService;
        $this->ajaxMess = $ajaxMess;
    }
    
    /**
     * @Route("/card/link/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ServiceCircularReferenceException
     */
    public function addAction(Request $request) : JsonResponse
    {
        $card = $request->get('card', '');
        if (empty($card)) {
            return $this->ajaxMess->getEmptyCardNumber();
        }

        if($card){
            $card = preg_replace("/\D/", '', $card);
        }
    
        try {
            if ($this->bonusService->activateBonusCard($card)) {
                return JsonSuccessResponse::create(
                    'Карта привязана',
                    200,
                    [],
                    ['reload' => true]
                );
            }
        } catch (NotAuthorizedException $e) {
            return $this->ajaxMess->getNeedAuthError();
        } catch (ApplicationCreateException|ManzanaServiceException $e) {
            /** показываем общую ошибку */
        } catch (CardNotValidException $e) {
            return $this->ajaxMess->getCardNotValidError();
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }
}
