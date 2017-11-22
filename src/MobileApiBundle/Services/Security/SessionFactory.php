<?php

namespace FourPaws\MobileApiBundle\Services\Security;

use Bitrix\Main\Loader;
use FourPaws\MobileApiBundle\Entity\Session;
use FourPaws\MobileApiBundle\Exception\InvalidIdentifierException;

class SessionFactory
{
    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    public function __construct(TokenGeneratorInterface $tokenGenerator)
    {
        Loader::includeModule('sale');
        $this->tokenGenerator = $tokenGenerator;
    }

    public function create(): Session
    {
        $session = (new Session())
            ->setUserAgent($this->getUserAgent())
            ->setFUserId($this->getBasketUserId())
            ->setToken($this->tokenGenerator->generate());
        $this->configIp($session);
        return $session;
    }

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

    protected function configIp(Session $session): Session
    {
        return $session
            ->setRemoteAddress($_SERVER['REMOTE_ADDR'] ?: '')
            ->setHttpClientIp($_SERVER['HTTP_CLIENT_IP'] ?: '')
            ->setHttpXForwardedFor($_SERVER['HTTP_X_FORWARDED_FOR'] ?: '');
    }
}
