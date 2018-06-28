<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Service\BonusService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */

class FourPawsPersonalCabinetBonusComponent extends CBitrixComponent
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
        $this->bonusService = $container->get('bonus.service');
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['CACHE_TIME'] = 360000;
        $params['MANZANA_CACHE_TIME'] = 10 * 60;
        return $params;
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
    public function executeComponent()
    {
        $instance = Application::getInstance();

        try {
            $user = $this->currentUserProvider->getCurrentUser();
        } catch (NotAuthorizedException $e) {
            /** запрашиваем авторизацию */
            \define('NEED_AUTH', true);
            return null;
        }

        if (!$user->havePersonalPhone()) {
            $this->includeComponentTemplate('notPhone');
            return false;
        }

        $cardNumber = $user->getDiscountCardNumber();
        $cache = $instance->getCache();

        $cachePath = $this->getCachePath() ?: $this->getPath();
        if ($cache->initCache($this->arParams['MANZANA_CACHE_TIME'],
            serialize(['userId' => $user->getId(), 'card' => $cardNumber]), $cachePath)) {
            $result = $cache->getVars();
            $this->arResult['BONUS'] = $bonus = $result['bonus'];
        } elseif ($cache->startDataCache()) {
            $tagCache = new TaggedCacheHelper($cachePath);
            $tagCache->addTags([
                'personal:bonus:' . $user->getId(),
                'user:' . $user->getId(),
            ]);

            try {
                $this->arResult['BONUS'] = $bonus = $this->bonusService->getUserBonusInfo($user);
            } catch (NotAuthorizedException $e) {
                /** запрашиваем авторизацию */
                \define('NEED_AUTH', true);
                $cache->abortDataCache();
                $tagCache->abortTagCache();
                return null;
            }

            /** @todo здесь тоже можно делать обновление динамически, так как это влияет только на товары */
            /** обновим толкьо когда получаем - когда закеширвоана - нафг нам постоянно обновлять и создавать нагрузку */
            $this->currentUserProvider->refreshUserBonusPercent($user, $bonus);

            $tagCache->end();
            $cache->endDataCache(['bonus' => $bonus]);
        }

        $this->setFrameMode(true);

        if ($this->startResultCache($this->arParams['CACHE_TIME'], [
            'userId'       => $user->getId(),
            'cardNumber'   => $bonus->getCard()->getCardNumber(),
            'bonus'        => $bonus->getActiveBonus(),
            'sum'          => $bonus->getSum(),
            'paidByBonus'  => $bonus->getCredit(),
            'realDiscount' => $bonus->getRealDiscount(),
        ], $cachePath)) {
            (new TaggedCacheHelper($cachePath))->addTags([
                'personal:bonus:' . $user->getId(),
                'order:' . $user->getId(),
                'user:' . $user->getId(),
            ]);

            $this->includeComponentTemplate();
        }

        return true;
    }
}
