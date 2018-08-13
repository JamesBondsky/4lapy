<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\GroupTable;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\Enum\UserGroup;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Entity\Referral;
use FourPaws\PersonalBundle\Service\ReferralService;
use FourPaws\UserBundle\Entity\User;
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
    /**
     * @var ReferralService
     */
    private $referralService;

    /** @var UserAuthorizationInterface */
    private $authUserProvider;

    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;
    /** @var User */
    private $curUser;
    /** @var string */
    private $cachePath;
    /** @var Application */
    private $instance;
    /** @var \Bitrix\Main\Data\Cache */
    private $cache;

    /**
     * FourPawsPersonalCabinetReferralComponent constructor.
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
        $this->referralService = $container->get('referral.service');
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->authUserProvider = $container->get(UserAuthorizationInterface::class);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['PAGE_COUNT'] = 10;
        /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
        /** кешируем на сутки, можно будет увеличить если обновления будут не очень частые - чтобы лишний кеш не хранился */
        $params['CACHE_TIME'] = 24 * 60 * 60;
        /** манзана кешируется на час */
        $params['MANZANA_CACHE_TIME'] = 60 * 60;
        return $params;
    }

    /**
     * {@inheritdoc}
     * @throws ObjectException
     * @throws EmptyEntityClass
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
        if (!$this->checkPermissions()) {
            return null;
        }

        $this->init();
        /** @var PageNavigation $nav */
        $nav = $this->arResult['NAV'];
        if ($this->cache->initCache($this->arParams['MANZANA_CACHE_TIME'],
            serialize([
                'userId'        => $this->curUser->getId(),
                'page'          => $nav->getCurrentPage(),
                'search'        => $this->arResult['search'],
                'referral_type' => $this->arResult['referralType'],
            ]),
            $this->cachePath)) {
            $result = $this->cache->getVars();
            $nav = $result['NAV'];
            $this->arResult['BONUS'] = $result['BONUS'];
            $cacheItems = $result['cacheItems'];

            $this->arResult['COUNT'] = $result['COUNT'];
            $this->arResult['COUNT_ACTIVE'] = $result['COUNT_ACTIVE'];
            $this->arResult['COUNT_MODERATE'] = $result['COUNT_MODERATE'];
        } elseif ($this->cache->startDataCache()) {
            $tagCache = new TaggedCacheHelper($this->cachePath);

            $cacheItems = $this->loadData($nav, $tagCache);
            $this->loadCounters();

            $tagCache->addTags([
                'personal:referral:' . $this->curUser->getId(),
                'hlb:field:referral_user:' . $this->curUser->getId(),
            ]);

            $tagCache->end();
            $this->cache->endDataCache([
                'NAV'        => $nav,
                'BONUS'      => $this->arResult['BONUS'],
                'cacheItems' => $cacheItems,

                'COUNT'          => $this->arResult['COUNT'],
                'COUNT_ACTIVE'   => $this->arResult['COUNT_ACTIVE'],
                'COUNT_MODERATE' => $this->arResult['COUNT_MODERATE'],
            ]);
        }

        $this->arResult['NAV'] = $nav;

        $this->showTemplate($cacheItems);


        return true;
    }

    protected function showTemplate($cacheItems): void
    {
        /** @var PageNavigation $nav */
        $nav = $this->arResult['NAV'];
        if ($this->startResultCache(
            $this->arParams['CACHE_TIME'],
            [
                'cacheItems'    => $cacheItems,
                'count'         => $nav->getRecordCount(),
                'page'          => $nav->getCurrentPage(),
                'bonus'         => $this->arResult['BONUS'],
                'search'        => $this->arResult['search'],
                'referral_type' => $this->arResult['referralType'],
            ],
            $this->cachePath
        )) {
            TaggedCacheHelper::addManagedCacheTags([
                'personal:referral',
                'personal:referral:' . $this->curUser->getId(),
                'hlb:field:referral_user:' . $this->curUser->getId(),
            ]);

            $this->arResult['referral_type'] = $this->referralService->getReferralType();
            $this->arResult['FORMATED_BONUS'] = \number_format($this->arResult['BONUS'], 0, '.', ' ');

            $this->includeComponentTemplate();
        }
    }

    protected function checkPermissions(): bool
    {
        if (!$this->authUserProvider->isAuthorized()) {
            define('NEED_AUTH', true);

            return false;
        }

        try {
            $this->curUser = $this->currentUserProvider->getCurrentUser();
            $optId = (int)GroupTable::query()->setFilter(['STRING_ID' => UserGroup::OPT_CODE])->setLimit(1)->setSelect(['ID'])->setCacheTtl(360000)->exec()->fetch()['ID'];
            if ($optId === 0) {
                $optId = UserGroup::OPT_ID;
            }
            if (!\in_array($optId, $this->currentUserProvider->getUserGroups(), true)) {
                LocalRedirect('/personal');
            }
        } catch (NotAuthorizedException $e) {
            define('NEED_AUTH', true);

            return false;
        }

        return true;
    }

    protected function redirect(TaggedCacheHelper $tagCache): void
    {
        $tagCache->abortTagCache();
        $this->cache->abortDataCache();
        TaggedCacheHelper::clearManagedCache(['personal:referral:' . $this->curUser->getId()]);
        LocalRedirect($this->request->getRequestUri());
        die();
    }

    /**
     * @throws SystemException
     */
    protected function init(): void
    {
        $this->instance = Application::getInstance();

        $this->setFrameMode(true);

        $this->arResult['ITEMS'] = new ArrayCollection();

        $nav = new PageNavigation('nav-referral');
        $nav->allowAllRecords(false)->setPageSize($this->arParams['PAGE_COUNT'])->initFromUri();

        $this->arResult['NAV'] = $nav;
        $this->cache = $this->instance->getCache();
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->request = $this->instance->getContext()->getRequest();
        $this->arResult['search'] = (string)$this->request->get('search');
        $this->arResult['referralType'] = (string)$this->request->get('referral_type');
        $this->cachePath = $this->getCachePath() ?: $this->getPath();
    }

    protected function loadData(PageNavigation $nav, TaggedCacheHelper $tagCache)
    {
        $cacheItems = $items = new ArrayCollection();
        try {
            /** @var ArrayCollection $items
             * @var bool $redirect
             */
            $main = empty($this->arResult['referralType']) && empty($this->arResult['search']);
            [$items, $redirect, $this->arResult['BONUS']] = $this->referralService->getCurUserReferrals($nav,
                $main);
            if ($this->arResult['BONUS'] > 0) {
                /** отбрасываем дробную часть - нужно ли? */
                $this->arResult['BONUS'] = floor($this->arResult['BONUS']);
            }
            if ($redirect) {
                $this->redirect($tagCache);
            }
            $this->arResult['ITEMS'] = $items;
        } catch (NotAuthorizedException $e) {
            define('NEED_AUTH', true);
            $tagCache->abortTagCache();
            $this->cache->abortDataCache();
            return null;
        }

        if (!$items->isEmpty()) {
            /** @var Referral $item */
            /** @noinspection ForeachSourceInspection */
            foreach ($items as $item) {
                if ($item instanceof Referral) {
                    $cardId = $item->getCard();
                    $cacheItems[$cardId] = [
                        'bonus'     => $item->getBonus(),
                        'card'      => $cardId,
                        'moderated' => $item->isModerate(),
//                            'dateEndActive' => $item->getDateEndActive(),
                    ];
                }
            }
        }

        return $cacheItems;
    }

    protected function loadCounters(): void
    {
        $this->arResult['COUNT'] = $this->referralService->getAllCountByUser();
        $this->arResult['COUNT_ACTIVE'] = $this->referralService->getActiveCountByUser();
        $this->arResult['COUNT_MODERATE'] = $this->referralService->getModeratedCountByUser();
    }
}
