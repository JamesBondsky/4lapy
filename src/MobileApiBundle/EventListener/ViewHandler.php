<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\EventListener;

use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\View\View;
use FourPaws\App\Application as App;
use FourPaws\External\ManzanaService;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ViewHandler implements EventSubscriberInterface
{

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', 50],
        ];
    }

    public function onKernelView(GetResponseForControllerResultEvent $event): bool
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return false;
        }

        $view = $event->getControllerResult();
        if (!($view instanceof Response) && !($view instanceof View)) {
            $event->setControllerResult(new Response($view));
        }
        $this->sendMobileMark();
        return true;
    }

    private function sendMobileMark()
    {
        $container = App::getInstance()->getContainer();

        /** @var ManzanaService $manzanaService */
        $manzanaService = $container->get('manzana.service');
        try {
            /** @var UserService $userCurrentUserService */
            $userCurrentUserService = $container->get(CurrentUserProviderInterface::class);
            $currentUser = $userCurrentUserService->getCurrentUser();
            $userId = $currentUser->getId();
            $personalPhone = $currentUser->getPersonalPhone();

            $manzanaService->updateContactMobileAsync(['userId' => $userId, 'personalPhone' => $personalPhone]);
        } catch (NotAuthorizedException $e) {
        }
    }
}
