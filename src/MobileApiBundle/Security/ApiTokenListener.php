<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Security;

use FourPaws\MobileApiBundle\Exception\InvalidSignRequestException;
use FourPaws\MobileApiBundle\Exception\InvalidTokenException;
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

    /**
     * @param GetResponseEvent $event
     *
     * @throws \FourPaws\MobileApiBundle\Exception\InvalidTokenException
     * @throws \FourPaws\MobileApiBundle\Exception\InvalidSignRequestException
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$this->signChecker->handle($request)) {
            throw new InvalidSignRequestException('Invalid sign provided');
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
        throw new InvalidTokenException('Invalid token provided');
    }
}
