<?php

namespace FourPaws\MobileApiBundle\Security;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\MobileApiBundle\Entity\ApiUserSession;
use FourPaws\MobileApiBundle\Exception\InvalidTokenException;
use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;
use FourPaws\MobileApiBundle\Traits\MobileApiLoggerAwareTrait;
use FourPaws\UserBundle\Security\BitrixUserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;

class ApiTokenProvider implements AuthenticationProviderInterface
{
    use MobileApiLoggerAwareTrait;

    /**
     * @var ApiUserSessionRepository
     */
    private $sessionRepository;

    /**
     * @var \CUser
     */
    private $cUser;

    /**
     * @var BitrixUserProviderInterface
     */
    private $bitrixUserProvider;

    public function __construct(BitrixUserProviderInterface $bitrixUserProvider, ApiUserSessionRepository $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
        $this->cUser = new \CUser();
        $this->bitrixUserProvider = $bitrixUserProvider;

        $this->setLogger(LoggerFactory::create('ApiTokenProvider', 'mobileApi'));
    }

    /**
     * Attempts to authenticate a TokenInterface object.
     *
     * @param PreAuthenticationApiToken|TokenInterface $token The TokenInterface instance to authenticate
     *
     * @return ApiToken An authenticated TokenInterface instance, never null
     */
    public function authenticate(TokenInterface $token): ApiToken
    {
        $session = $this->sessionRepository->findByToken($token->getToken());

        try {
            $this->logger->info('session', [
                'session' => print_r($session, true),
                'token' => print_r($token, true)
            ]);
        } catch (\Exception $e) {}

        if (!$session) {
            throw new InvalidTokenException('Invalid token provided');
        }

        $user = null;
        // if there is userID in the session - authorize this user,
        // otherwise initialize user basket and return a token
        if ($this->initBySession($session) && $session->getUserId()) {
            $user = $this->bitrixUserProvider->loadUserById($session->getUserId());
            $user->getRolesCollection()->add(new Role('ROLE_API'));

            $this->logger->info('find session', [
                'session_user_id' => $session->getUserId(),
                'user_id' => $user->getId(),
                'fuser' => $session->getFUserId(),
                'session' => $_SESSION,
                'auth_multisite' => \COption::GetOptionString("main", "auth_multisite", "N"),
            ]);
        } else {
            $this->logger->info('init new session', [
                'fuser' => $session->getFUserId(),
                'user_id' => $session->getUserId()
            ]);
            $this->initFUserIdByToken($session->getFUserId());
        }


        return new ApiToken(
            $user ? $user->getRoles() : ['ROLE_API'],
            $session,
            $user
        );
    }

    /**
     * Checks whether this provider supports the given token.
     *
     * @param TokenInterface $token
     *
     * @return bool true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token): bool
    {
        return $token instanceof PreAuthenticationApiToken;
    }

    protected function initBySession(ApiUserSession $session)
    {
        if ($session->getUserId()) {
            global $USER;
            return $USER->Authorize($session->getUserId());
            return \CUser::Authorize($session->getUserId());
            return $this->cUser->Authorize($session->getUserId());
        }
        return false;
    }

    /**
     * Для неавторизованных пользователей получаем ID корзины (FUSER_ID) по токену из таблички с сессиями
     * И подсовываем полученный FUSER_ID в сессию, чтобы битрикс подтягивал нужную корзину
     * @see \CAllSaleBasket::GetID()
     *
     * @param int $fUserId
     */
    protected function initFUserIdByToken(int $fUserId): void
    {
        $_SESSION['SALE_USER_ID'] = $fUserId;
    }
}
