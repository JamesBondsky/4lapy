<?php

namespace FourPaws\UserBundle\Security;

use FourPaws\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class BitrixAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var \CAllUser|\CUser
     */
    private $cUser;

    public function __construct()
    {
        global $USER;
        $this->cUser = $USER;
    }

    /**
     * @inheritdoc
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse('/');
    }

    /**
     * @inheritdoc
     */
    public function getCredentials(Request $request)
    {
        return $this->cUser->IsAuthorized() ? [
            'IS_ADMIN' => (bool)$this->cUser->IsAdmin(),
            'ID'       => (int)$this->cUser->GetID(),
        ] : null;
    }

    /**
     * @inheritdoc
     * @param BitrixUserProviderInterface|UserProviderInterface $userProvider
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $id = $credentials['ID'] ?? 0;
        if ($id <= 0) {
            throw new AuthenticationException('Пользователь не авторизован или не получилось получить его идентификатор');
        }

        $user = $userProvider->loadUserById($id);
        if ($user) {
            $isAdmin = $credentials['IS_ADMIN'] ?? false;
            $this->cUser->GetUserGroupArray();
            /**
             * @todo dynamic admin roles
             */
            if ($isAdmin) {
                $user->getRoles();
            }
        }
        return $user;
    }

    /**
     * @inheritdoc
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $id = $credentials['ID'] ?? 0;
        if ($id <= 0) {
            throw new AuthenticationException('Пользователь не авторизован или не получилось получить его идентификатор');
        }
        return $user instanceof User;
    }

    /**
     * @inheritdoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function supportsRememberMe(): bool
    {
        return false;
    }
}
