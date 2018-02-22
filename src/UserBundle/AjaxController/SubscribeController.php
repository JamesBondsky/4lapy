<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\AjaxController;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\External\Exception\ExpertsenderServiceException;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
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
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     */
    public function subscribeAction(Request $request): JsonResponse
    {
        $type = $request->get('type', '');
        $email = $request->get('email', '');

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->ajaxMess->getWrongEmailError();
        }

        $success = false;

        if (\in_array('sale', $type, true)) {
            if ($type['sale'] === 'Y') {
                /** @todo реализовать подписку на подарки */
                $success = true;
            }
        } else {
            /** @todo удаление подписки на подарки */
        }

        /** В эксперт сендере только о новостях список есть */
        if (\in_array('material', $type, true)) {
            try {
                try {
                    $userService = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
                    $user = $userService->getCurrentUser();
                } catch (NotAuthorizedException $e) {
                    $user = new User();
                }
                $user->setEmail($email);
                $expertSenderService = Application::getInstance()->getContainer()->get('expertsender.service');
                if ($expertSenderService->sendEmailSubscribeNews($user)) {
                    $success = true;
                } else {
                    $success = false;
                }
            } catch (ExpertsenderServiceException $e) {
            } catch (ApplicationCreateException $e) {
            }
        } else {
            /** @todo удаление из подписки */
            try {
                $user = new User();
                $user->setEmail($email);
                $expertSenderService = Application::getInstance()->getContainer()->get('expertsender.service');
                if ($expertSenderService->sendEmailUnSubscribeNews($user)) {
                    $success = true;
                } else {
                    $success = false;
                }
            } catch (ExpertsenderServiceException $e) {
            } catch (ApplicationCreateException $e) {
            }
        }

        if ($success) {
            return JsonSuccessResponse::create('Ваша подписка успешно изменена');
        }

        return $this->ajaxMess->getSystemError();
    }
}
