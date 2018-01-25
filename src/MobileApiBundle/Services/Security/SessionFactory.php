<?php

namespace FourPaws\MobileApiBundle\Services\Security;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use FourPaws\MobileApiBundle\Entity\Session;
use FourPaws\MobileApiBundle\Exception\BitrixException;
use FourPaws\MobileApiBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;

class SessionFactory
{
    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;
    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;

    public function __construct(TokenGeneratorInterface $tokenGenerator, CurrentUserProviderInterface $currentUserProvider)
    {
        try {
            Loader::includeModule('sale');
        } catch (LoaderException $e) {
            throw new BitrixException($e->getMessage(), $e->getCode(), $e);
        }
        $this->tokenGenerator = $tokenGenerator;
        $this->currentUserProvider = $currentUserProvider;
    }

    /**
     * @throws \FourPaws\MobileApiBundle\Exception\InvalidIdentifierException
     * @return Session
     */
    public function create(): Session
    {
        $session = (new Session())
            ->setUserAgent($this->getUserAgent())
            ->setFUserId($this->getBasketUserId())
            ->setToken($this->tokenGenerator->generate());
        $this->configIp($session);
        return $session;
    }

    /**
     * @param Session $session
     *
     * @return Session
     */
    public function update(Session $session): Session
    {
        $session
            ->setUserAgent($this->getUserAgent());
        $this->configIp($session);
        return $session;
    }

    /**
     * @return string
     */
    protected function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * @throws InvalidIdentifierException
     * @return int
     */
    protected function getBasketUserId(): int
    {
        if ($basketId = \CSaleBasket::GetBasketUserID()) {
            return $basketId;
        }
        throw new InvalidIdentifierException('Cant create basket user id');
    }

    /**
     * @param Session $session
     *
     * @return Session
     */
    protected function configIp(Session $session): Session
    {
        return $session
            ->setRemoteAddress($_SERVER['REMOTE_ADDR'] ?: '')
            ->setHttpClientIp($_SERVER['HTTP_CLIENT_IP'] ?: '')
            ->setHttpXForwardedFor($_SERVER['HTTP_X_FORWARDED_FOR'] ?: '');
    }
}
