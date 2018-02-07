<?php

namespace FourPaws\MobileApiBundle\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class ApiTokenListener implements ListenerInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var SignCheckerInterface
     */
    private $signChecker;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        SignCheckerInterface $signChecker
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->signChecker = $signChecker;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$this->signChecker->handle($request)) {
            $event->setResponse(new JsonResponse(['errors' => [2]], Response::HTTP_UNAUTHORIZED));
            return;
        }

        $token = $request->get('token', '');
        if (!$token) {
            $this->tokenStorage->setToken(new ApiToken(['ROLE_NO_TOKEN']));
            return;
        }

        $preAuthenticationApiToken = new PreAuthenticationApiToken([], $token);
        try {
            $authToken = $this->authenticationManager->authenticate($preAuthenticationApiToken);
            $this->tokenStorage->setToken($authToken);
            return;
        } catch (AuthenticationException $exception) {
        }
        $event->setResponse(new JsonResponse(['errors' => [1]], Response::HTTP_UNAUTHORIZED));
    }
}
