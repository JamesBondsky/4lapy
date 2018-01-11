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
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\PersonalBundle\Entity\Referral;
use FourPaws\PersonalBundle\Service\ReferralService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsPersonalCabinetReferralComponent extends CBitrixComponent
{
    /**
     * @var ReferralService
     */
    private $referralService;
    
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
        $this->referralService = $container->get('address.service');
    }
    
    /**
     * {@inheritdoc}
     * @throws ManzanaServiceException
     * @throws \Exception
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws LoaderException
     */
    public function executeComponent()
    {
        $this->setFrameMode(true);
        
        $this->arResult['ITEMS'] = $this->referralService->getCurUserReferrals();
        $this->arResult['BONUS'] = 0;
        $cacheItems              = [];
        if (\is_array($this->arResult['ITEMS']) && !empty($this->arResult['ITEMS'])) {
            /** @var Referral $item */
            /** @noinspection ForeachSourceInspection */
            foreach ($this->arResult['ITEMS'] as $item) {
                $this->arResult['BONUS'] += $item->getBonus();
                $cardId                  = $item->getCard();
                $cacheItems[$cardId]     = [
                    'bonus'         => $item->getBonus(),
                    'card'          => $cardId,
                    'moderated'     => $item->isModerate(),
                    'dateEndActive' => $item->getDateEndActive(),
                ];
            }
            if ($this->arResult['BONUS'] > 0) {
                $this->arResult['BONUS'] = floor($this->arResult['BONUS']);
            }
            $this->arResult['FORMATED_BONUS'] = \number_format($this->arResult['BONUS'], 0, '.', ' ');
            
        }
        /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
        /** кешируем на сутки, можно будет увеличить если обновления будут не очень частые - чтобы лишний кеш не хранился */
        $cacheTime = 24 * 60 * 60;
        if ($this->startResultCache($cacheTime,
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
