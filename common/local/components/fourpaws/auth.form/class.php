<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;

class FourPawsAuthFormComponent extends \CBitrixComponent
{
    const MODE_PROFILE = 0;

    const MODE_FORM = 1;

    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;

    /**
     * @var UserAuthorizationInterface
     */
    private $userAuthorizationService;

    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = Application::getInstance()->getContainer();
        } catch (\FourPaws\App\Exceptions\ApplicationCreateException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->userAuthorizationService = $container->get(UserAuthorizationInterface::class);
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->getUserAuthorizationService()->isAuthorized() ? static::MODE_PROFILE : static::MODE_FORM;
    }

    /**
     * @return array
     */
    public function getAuthSocialService()
    {
        return (new CSocServAuthManager())->GetActiveAuthServices([]);
    }

    /**
     * @return CurrentUserProviderInterface
     */
    public function getCurrentUserProvider(): CurrentUserProviderInterface
    {
        return $this->currentUserProvider;
    }

    /**
     * @return UserAuthorizationInterface
     */
    public function getUserAuthorizationService(): UserAuthorizationInterface
    {
        return $this->userAuthorizationService;
    }
}
