<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\PersonalBundle\Entity\Referral;
use FourPaws\PersonalBundle\Service\ReferralService;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsPersonalCabinetReferralComponent extends CBitrixComponent
{
    protected static $accessUserGroup = 30;
    
    /**
     * @var ReferralService
     */
    private $referralService;
    
    /** @var UserAuthorizationInterface */
    private $authUserProvider;
    
    /**
     * @var CurrentUserProviderInterface
     */
    private          $currentUserProvider;
    
    /**
     * AutoloadingIssuesInspection constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
        $this->referralService     = $container->get('referral.service');
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->authUserProvider    = $container->get(UserAuthorizationInterface::class);
    }
    
    /**
     * {@inheritdoc}
     * @throws SystemException
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @throws \Exception
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws LoaderException
     */
    public function executeComponent()
    {
        if (!$this->authUserProvider->isAuthorized()) {
            define('NEED_AUTH', true);
            
            return null;
        }
        
        try {
            if (!\in_array((int)static::$accessUserGroup, $this->currentUserProvider->getUserGroups(), true)) {
                LocalRedirect('/personal');
            }
        } catch (NotAuthorizedException $e) {
            define('NEED_AUTH', true);
            
            return null;
        }
        $this->setFrameMode(true);
        
        try {
            $arResult['NAV'] = new PageNavigation("nav-referral");
            $arResult['NAV']->allowAllRecords(false)->setPageSize(10)->initFromUri();
            
            $this->arResult['ITEMS'] = $this->referralService->getCurUserReferrals(true, $arResult['NAV']);
        } catch (NotAuthorizedException $e) {
        } catch (CardNotFoundException $e) {
        }
        $this->arResult['COUNT']          = $this->referralService->getAllCountByUser();
        $this->arResult['COUNT_ACTIVE']   = $this->referralService->getActiveCountByUser();
        $this->arResult['COUNT_MODERATE'] = $this->referralService->getModeratedCountByUser();
        $this->arResult['BONUS']          = 0;
        $cacheItems                       = [];
        $arResult['referral_type']        = $this->referralService->getReferralType();
        if (\is_array($this->arResult['ITEMS']) && !empty($this->arResult['ITEMS'])) {
            /** @var Referral $item */
            /** @noinspection ForeachSourceInspection */
            foreach ($this->arResult['ITEMS'] as $item) {
                if ($item instanceof Referral) {
                    $this->arResult['BONUS'] += $item->getBonus();
                    $cardId                  = $item->getCard();
                    $cacheItems[$cardId]     = [
                        'bonus'         => $item->getBonus(),
                        'card'          => $cardId,
                        'moderated'     => $item->isModerate(),
                        'dateEndActive' => $item->getDateEndActive(),
                    ];
                }
            }
            if ($this->arResult['BONUS'] > 0) {
                $this->arResult['BONUS'] = floor($this->arResult['BONUS']);
            }
            $this->arResult['FORMATED_BONUS'] = \number_format($this->arResult['BONUS'], 0, '.', ' ');
            
        }
        /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
        /** кешируем на сутки, можно будет увеличить если обновления будут не очень частые - чтобы лишний кеш не хранился */
        $cacheTime = 24 * 60 * 60;
        if ($this->startResultCache(
            $cacheTime,
            [
                'items' => $cacheItems,
                'bonus' => $this->arResult['BONUS'],
            ]
        )) {
            $this->includeComponentTemplate();
        }
        
        return true;
    }
}
