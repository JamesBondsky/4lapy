<?php

namespace FourPaws\MobileApiBundle\Services\Security;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use FourPaws\MobileApiBundle\Entity\ApiUserSession;
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
     * @return ApiUserSession
     */
    public function create(): ApiUserSession
    {
        $session = (new ApiUserSession())
            ->setUserAgent($this->getUserAgent())
            ->setFUserId($this->getBasketUserId())
            ->setToken($this->tokenGenerator->generate());
        $this->configIp($session);
        return $session;
    }

    /**
     * @param ApiUserSession $session
     *
     * @return ApiUserSession
     */
    public function update(ApiUserSession $session): ApiUserSession
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
     * @param ApiUserSession $session
     *
     * @return ApiUserSession
     */
    protected function configIp(ApiUserSession $session): ApiUserSession
    {
        return $session
            ->setRemoteAddress($_SERVER['REMOTE_ADDR'] ?: '')
            ->setHttpClientIp($_SERVER['HTTP_CLIENT_IP'] ?: '')
            ->setHttpXForwardedFor($_SERVER['HTTP_X_FORWARDED_FOR'] ?: '');
    }
}
