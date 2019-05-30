<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\PersonalBundle\Service\BonusService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */

class FourPawsPersonalCabinetBonusComponent extends FourPawsComponent
{
    /**
     * @var BonusService
     */
    private $bonusService;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /**
     * AutoloadingIssuesInspection constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        $container = App::getInstance()->getContainer();
        $this->bonusService = $container->get('bonus.service');
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
    }

    /**
     * {@inheritdoc}
     * @throws ArgumentException
     * @throws \Exception
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws LoaderException
     */
    public function prepareResult(): void
    {
        try {
            $user = $this->currentUserProvider->getCurrentUser();
            if (!$user->hasPhone()) {
                $this->setTemplatePage('notPhone');
                return;
            }
            $bonus = $this->bonusService->updateUserBonusInfo($user);
            $this->currentUserProvider->refreshUserBonusPercent($user, $bonus);

            $this->arResult['BONUS'] = $bonus;
        } catch (NotAuthorizedException $e) {
            /** запрашиваем авторизацию */
            \define('NEED_AUTH', true);
        }
    }
}
