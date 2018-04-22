<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsExpertsenderFormComponent extends \CBitrixComponent
{
    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;
    
    /**
     * @var UserAuthorizationInterface
     */
    private $authorizationProvider;
    
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = Application::getInstance()->getContainer();
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->authorizationProvider = $container->get(UserAuthorizationInterface::class);
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        } catch (ApplicationCreateException $e) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
    }
    
    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $this->arResult['EMAIL'] = '';
            if ($this->getAuthorizationProvider()->isAuthorized()) {
                $curUser = $this->getCurrentUserProvider()->getCurrentUser();
                $this->arResult['EMAIL'] = $curUser !== null ? $curUser->getEmail() : '';
                $this->arResult['CONFIRMED'] = $curUser !== null ?  $curUser->isEmailConfirmed() : false;
                $this->arResult['IS_SUBSCRIBED'] = $curUser->isEsSubscribed();
            }
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
     * @return UserAuthorizationInterface
     */
    public function getAuthorizationProvider() : UserAuthorizationInterface
    {
        return $this->authorizationProvider;
    }
    
    /**
     * @return CurrentUserProviderInterface
     */
    public function getCurrentUserProvider() : CurrentUserProviderInterface
    {
        return $this->currentUserProvider;
    }
}
