<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Exceptions\ApplicationCreateException;
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
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
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
    /** @var UserAuthorizationInterface  */
    private $userAuthorization;

    public function __construct(
        BonusService $bonusService,
        UserAuthorizationInterface $userAuthorization,
        AjaxMess $ajaxMess
    )
    {
        $this->userAuthorization = $userAuthorization;
        $this->bonusService = $bonusService;
        $this->ajaxMess = $ajaxMess;
    }
    
    /**
     * @Route("/card/link/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addAction(Request $request) : JsonResponse
    {
        if(!$this->userAuthorization->isAuthorized()){
            return $this->ajaxMess->getNeedAuthError();
        }
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
        } catch (ManzanaServiceException $e) {
            $logger = LoggerFactory::create('manzana');
            $logger->error('Ошибка манзаны - '. $e->getMessage());
        } catch (CardNotValidException $e) {
            return $this->ajaxMess->getCardNotValidError();
        } catch (BitrixRuntimeException $e) {
            return $this->ajaxMess->getUpdateError($e->getMessage());
        }
        catch (ApplicationCreateException|ServiceCircularReferenceException|ServiceNotFoundException $e){
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов '. $e->getMessage());
        }
        catch (ConstraintDefinitionException|InvalidIdentifierException $e){
            $logger = LoggerFactory::create('params');
            $logger->critical('Ошибка параметров '. $e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }
}
