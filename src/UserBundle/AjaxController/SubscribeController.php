<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\External\Exception\ExpertsenderServiceException;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidArgumentException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SubscribeController
 *
 * @package FourPaws\UserBundle\AjaxController
 * @Route("/subscribe")
 */
class SubscribeController extends Controller
{

    /** @var AjaxMess */
    private $ajaxMess;

    public function __construct(AjaxMess $ajaxMess)
    {
        $this->ajaxMess = $ajaxMess;
    }

    /**
     * @Route("/subscribe/", methods={"POST"})
     * @param Request $request
     *
     * @return JsonErrorResponse
     */
    public function subscribeAction(Request $request): JsonResponse
    {
        $type = $request->get('type', '');
        $email = $request->get('email', '');

        try{
            $container = Application::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            return $this->ajaxMess->getSystemError();
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->ajaxMess->getWrongEmailError();
        }

        $success = false;

        /** В эксперт сендере только о новостях список есть */
        if ($type === 'all') {
            try {
                try {
                    $userService = $container->get(CurrentUserProviderInterface::class);
                    $user = $userService->getCurrentUser();
                } catch (NotAuthorizedException $e) {
                    $user = new User();
                }
                $user->setEmail($email);
                $expertSenderService = $container->get('expertsender.service');
                if ($expertSenderService->sendEmailSubscribeNews($user)) {
                    $success = true;
                } else {
                    $success = false;
                }
            } catch (ExpertsenderServiceException $e) {
                $success = false;
                $logger = LoggerFactory::create('expertSender');
                $logger->critical('ES error - ' . $e->getMessage());
            } catch (ServiceNotFoundException|ServiceCircularReferenceException $e) {
                $success = false;
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            } catch (\BadMethodCallException|InvalidIdentifierException|ConstraintDefinitionException|\InvalidArgumentException $e) {
                $success = false;
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
            } catch (\RuntimeException|\Exception $e) {
                $success = false;
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            }
        } else {
            try {
                $user = new User();
                $user->setEmail($email);
                $expertSenderService = $container->get('expertsender.service');
                if ($expertSenderService->sendEmailUnSubscribeNews($user)) {
                    $success = true;
                } else {
                    $success = false;
                }
            } catch (ExpertsenderServiceException $e) {
                $success = false;
                $logger = LoggerFactory::create('expertSender');
                $logger->critical('ES error - ' . $e->getMessage());
            } catch (ServiceNotFoundException|ServiceCircularReferenceException $e) {
                $success = false;
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            } catch (\BadMethodCallException|InvalidIdentifierException|ConstraintDefinitionException|\InvalidArgumentException $e) {
                $success = false;
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
            } catch (\RuntimeException|\Exception $e) {
                $success = false;
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
            }
        }

        if ($success) {
            return JsonSuccessResponse::create('Ваша подписка успешно изменена');
        }

        return $this->ajaxMess->getSystemError();
    }
}
