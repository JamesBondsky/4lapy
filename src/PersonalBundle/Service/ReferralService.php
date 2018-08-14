<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\Manzana\Model\Referral as ManzanaReferal;
use FourPaws\External\Manzana\Model\ReferralParams as ManzanaReferalParams;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Entity\Referral;
use FourPaws\PersonalBundle\Repository\ReferralRepository;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ReferralService
 *
 * @package FourPaws\PersonalBundle\Service
 */
class ReferralService
{
    /**
     * @var ReferralRepository
     */
    public $referralRepository;

    /**
     * @var ManzanaService
     */
    public $manzanaService;

    /** @var CurrentUserProviderInterface $currentUser */
    private $currentUser;
    /** @var LoggerInterface */
    private $logger;
    /** @var bool */
    private $haveAdd = false;

    /**
     * ReferralService constructor.
     *
     * @param ReferralRepository $referralRepository
     * @param ManzanaService     $manzanaService
     *
     * @throws \RuntimeException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(ReferralRepository $referralRepository, ManzanaService $manzanaService)
    {
        $this->referralRepository = $referralRepository;
        $this->manzanaService = $manzanaService;
        $this->currentUser = App::getInstance()
            ->getContainer()
            ->get(CurrentUserProviderInterface::class);
        $this->logger = LoggerFactory::create('referral');
    }

    /**
     * @param PageNavigation|null $nav
     * @param bool                $main
     *
     * @throws \RuntimeException
     * @throws ObjectPropertyException
     * @throws EmptyEntityClass
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws SystemException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     * @return array
     */
    public function getCurUserReferrals(PageNavigation $nav = null, bool $main = true): array
    {
        $request = Application::getInstance()
            ->getContext()
            ->getRequest();
        $search = (string)$request->get('search');
        $referralType = $this->getReferralType();

        $filter = $this->getFilterByRequest($search, $referralType);
        if ($nav instanceof PageNavigation) {
            $this->referralRepository->setNav($nav);
        }
        $curUser = $this->referralRepository->curUserService->getCurrentUser();
        if (!empty($filter)) {
            $referrals = $this->getReferralsByFilter($curUser, $filter);
        } else {
            $referrals = $this->referralRepository->findByCurUser();
        }
        if ($nav !== null) {
            $nav = $this->referralRepository->getNav();
            $this->referralRepository->clearNav();
        }

        $needLoadAllItems = $nav->getPageCount() > 1;
        [
            ,
            $referrals,
            $allBonus,
        ] = $this->setDataByManzana($curUser, $referrals, $main, $needLoadAllItems);

        return [
            $referrals,
            $this->haveAdd,
            $allBonus,
        ];
    }

    /**
     * @throws SystemException
     * @return string
     */
    public function getReferralType(): string
    {
        $request = Application::getInstance()
            ->getContext()
            ->getRequest();
        $referralType = (string)$request->get('referral_type');
        $search = (string)$request->get('search');
        if (!empty($search)) {
            $referralType = 'all';
        }

        return $referralType;
    }

    /**
     * @param array $data
     *
     * @throws EmptyEntityClass
     * @throws ValidationException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @return bool
     */
    public function add(array $data): bool
    {
        if (empty($data['UF_USER_ID'])) {
            $data['UF_USER_ID'] = $this->currentUser->getCurrentUserId();
        }

        /** если уже есть такая карта не в статусе отмена модерации, то не добавляем */
        if ($this->existsReferralByCardNumber($data['UF_CARD'], $data['UF_USER_ID'])) {
            return false;
        }

        /** @var Referral $entity */
        $entity = $this->referralRepository->dataToEntity($data, Referral::class);

        return $this->referralRepository
            ->setEntity($entity)
            ->create();
    }

    /**
     * @param string $cardNumber
     *
     * @param string $userId
     *
     * @return bool
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function existsReferralByCardNumber(string $cardNumber, string $userId): bool
    {
        $filter = [
            [
                'LOGIC' => 'OR',
                ['UF_CARD' => $cardNumber, 'UF_USER_ID' => $userId],
                ['UF_CARD' => $cardNumber, ['LOGIC' => 'OR', 'UF_CANCEL_MODERATE' => 0, 'UF_CANCEL_MODERATE' => null]],
            ],
        ];
        $count = $this->referralRepository->getCount($filter);
        return $count > 0;
    }

    /**
     * @param Referral $referral
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ContactUpdateException
     * @return ManzanaReferalParams
     */
    public function getClientReferral(Referral $referral): ManzanaReferalParams
    {
        $client = new ManzanaReferalParams();

        $contactId = '';
        try {
            $contactId = $this->manzanaService->getContactIdByUser();
        } catch (ManzanaServiceContactSearchNullException $e) {
            $contactClient = new Client();
            try {
                $this->referralRepository->curUserService->setClientPersonalDataByCurUser($contactClient);

                try {
                    $res = $this->manzanaService->updateContact($contactClient);
                    $contactId = $res->contactId;
                } catch (ManzanaServiceException $e) {
                }
            } catch (NotAuthorizedException $e) {
            }
        } catch (NotAuthorizedException | ManzanaServiceException $e) {
        }
        if (!empty($contactId)) {
            $client->contactId = $contactId;
        }
        $client->cardNumber = $referral->getCard();
        $client->phone = $referral->getPhone();
        $client->email = $referral->getEmail();
        $client->lastName = $referral->getLastName();
        $client->secondName = $referral->getSecondName();
        $client->name = $referral->getName();

        return $client;
    }

    /**
     * @param array $data
     *
     * @throws EmptyEntityClass
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @return bool
     */
    public function update(array $data): bool
    {
        return $this->referralRepository->setEntityFromData($data, Referral::class)
            ->update();
    }

    /**
     * @return int
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getAllCountByUser(): int
    {
        try {
            return $this->referralRepository->getCount(
                ['UF_USER_ID' => $this->referralRepository->curUserService->getCurrentUserId()]
            );
        } catch (ObjectPropertyException | NotAuthorizedException $e) {
        }

        return 0;
    }

    /**
     * @return int
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getActiveCountByUser(): int
    {
        try {
            return $this->referralRepository->getCount([
                'UF_USER_ID'    => $this->referralRepository->curUserService->getCurrentUserId(),
                '!UF_MODERATED' => 1,
//                '!UF_CANCEL_MODERATE' => 1,
                ['LOGIC' => 'OR', 'UF_CANCEL_MODERATE' => 0, 'UF_CANCEL_MODERATE' => null],
            ]);
        } catch (ObjectPropertyException | NotAuthorizedException $e) {
        }

        return 0;
    }

    /**
     * @return int
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getModeratedCountByUser(): int
    {
        try {
            return $this->referralRepository->getCount(
                [
                    'UF_USER_ID'   => $this->referralRepository->curUserService->getCurrentUserId(),
                    'UF_MODERATED' => 1,
//                    '!UF_CANCEL_MODERATE' => 1,
                    ['LOGIC' => 'OR', 'UF_CANCEL_MODERATE' => 0, 'UF_CANCEL_MODERATE' => null],
                ]
            );
        } catch (ObjectPropertyException $e) {
        } catch (NotAuthorizedException $e) {
        }

        return 0;
    }

    /**
     * @return ArrayCollection|Referral[]
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getModeratedReferrals(): ArrayCollection
    {
        return $this->referralRepository->findBy([
            'filter' => [
                'UF_MODERATED' => 1,
//                '!UF_CANCEL_MODERATE' => 1,
                ['LOGIC' => 'OR', 'UF_CANCEL_MODERATE' => 0, 'UF_CANCEL_MODERATE' => null],
            ],
        ]);
    }

    /**
     * @param int $id
     * @param int $userId
     *
     * @return bool
     * @throws \Exception
     */
    public function delete(int $id, int $userId = 0): bool
    {
        $res = $this->referralRepository->delete($id);
        if ($res && $userId > 0) {
            TaggedCacheHelper::clearManagedCache([
                'personal:referral:' . $userId,
            ]);
        }

        return $res;
    }

    /** @todo разобраться с исключениями */
    /**
     * @param User            $curUser
     * @param ArrayCollection $referrals
     * @param bool            $main
     * @param bool            $needLoadAllItems
     *
     * @return array
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws NotAuthorizedException
     * @throws ObjectPropertyException
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws BitrixRuntimeException
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     * @throws \Exception
     */
    private function setDataByManzana(
        User $curUser,
        ArrayCollection $referrals,
        bool $main = true,
        bool $needLoadAllItems = true
    ): array {
        $arCards = [];
        $arReferralCards = [];
        $referralsList = [];
        $allBonus = 0;
        $this->haveAdd = false;
        $success = false;
        if (!$referrals->isEmpty()) {
            $referralsList = $referrals->toArray();
        }

        try {
            $manzanaReferrals = $this->manzanaService->getUserReferralList($curUser);
        } catch (ManzanaServiceException $e) {
            /** если нет данных от манзаны то дальнейшее выполнение бесполезно */
            $this->logger->critical('Ошибка манзаны - ' . $e->getMessage());

            return [
                $success,
                new ArrayCollection($referralsList),
                $allBonus,
            ];
        } catch (NotAuthorizedException $e) {
            /** прерываем выполнение если неавторизованы */
            return [
                $success,
                new ArrayCollection($referralsList),
                $allBonus,
            ];
        }

        /** если больше одной страницы грузим всех рефералов, чтобы не было дублирония при добавлении */
        if ($needLoadAllItems) {
            $fullReferralsList = $this->referralRepository->findByCurUser()
                ->toArray();
        } else {
            $fullReferralsList = $referralsList;
        }

        if (!empty($fullReferralsList)) {
            /** @var Referral $item */
            foreach ($fullReferralsList as $key => $item) {
                if (!empty($item->getCard())) {
                    $arCards[$item->getCard()] = $key;
                }
            }
        }

        if (!empty($arCards)) {
            if ($needLoadAllItems) {
                if (!empty($referralsList)) {
                    /** @var Referral $item */
                    foreach ($referralsList as $key => $item) {
                        if (!empty($item->getCard())) {
                            $arReferralCards[$item->getCard()] = $key;
                        }
                    }
                }
            } else {
                if (!empty($referralsList)) {
                    $arReferralCards = $arCards;
                }
            }
        }

        if (\is_array($manzanaReferrals) && !empty($manzanaReferrals)) {
            /** @var ManzanaReferal $item */
            foreach ($manzanaReferrals as $item) {
                $cardNumber = $item->cardNumber;
                if (empty($cardNumber)) {
                    continue;
                }
                $allBonus += (float)$item->sumReferralBonus;
                if (!\array_key_exists($cardNumber, $arReferralCards)) {
                    if (!$main) {
                        continue;
                    }
                    $this->setDataByNoneExistManzanaItemInSite($cardNumber, $curUser, $item);
                } else {
                    /** @var Referral $referral */
                    $referral = null;
                    if (array_key_exists($cardNumber, $arReferralCards)) {
                        $referral =& $fullReferralsList[$arReferralCards[$cardNumber]];
                    }
                    if ($referral !== null) {
                        $referral = $this->setDataByExistManzanaItemInSite($referral, $item);

                        if (array_key_exists($cardNumber, $arReferralCards)) {
                            $referralsList[$arReferralCards[$cardNumber]] = $referral;
                        }
                    }
                }
                unset($referral);
            }
        }
        $success = true;

        return [
            $success,
            new ArrayCollection($referralsList),
            $allBonus,
        ];
    }

    /**
     * @param $curUser
     * @param $filter
     *
     * @return ArrayCollection
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     */
    private function getReferralsByFilter(User $curUser, array $filter): ArrayCollection
    {
        $filter['UF_USER_ID'] = $curUserId = $curUser->getId();
        $cacheTime = 360000;

        $cache = Application::getInstance()->getCache();
        $referrals = new ArrayCollection();

        if ($cache->initCache($cacheTime,
            serialize($filter),
            __FUNCTION__ . '\ReferralsByFilter')) {
            $result = $cache->getVars();
            $referrals = $result['referrals'];
        } elseif ($cache->startDataCache()) {
            $tagCache = new TaggedCacheHelper(__FUNCTION__ . '\ReferralsByFilter');

            $referrals = $this->referralRepository->findBy(
                [
                    'filter' => $filter,
                ]
            );

            $tagCache->addTag('hlb:field:referral_user:' . $curUserId);

            $tagCache->end();
            $cache->endDataCache([
                'referrals' => $referrals,
            ]);
        }
        return $referrals;
}

    /**
     * @param $search
     * @param $referralType
     *
     * @return array
     */
    private function getFilterByRequest(string $search, string $referralType): array
    {
        $filter = [];
        if (!empty($search)) {
            $filter['?UF_CARD'] = $search;
        }
        if (!empty($referralType)) {
            switch ($referralType) {
                case 'active':
                    /** Дата окончания активности должна быть больше текущей даты */
                    /** @todo фильтр по дате активности карты */
//                    $filter['!UF_MODERATED'] = 1;
                    $filter[] = ['LOGIC' => 'OR', 'UF_MODERATED' => 0, 'UF_MODERATED' => null];
//                    $filter['!UF_CANCEL_MODERATE'] = 1;
                    $filter[] = ['LOGIC' => 'OR', 'UF_CANCEL_MODERATE' => 0, 'UF_CANCEL_MODERATE' => null];
                    break;
                case 'moderated':
                    $filter['UF_MODERATED'] = 1;
//                    $filter['!UF_CANCEL_MODERATE'] = 1;
                    $filter[] = ['LOGIC' => 'OR', 'UF_CANCEL_MODERATE' => 0, 'UF_CANCEL_MODERATE' => null];
                    break;
            }
        }
        return $filter;
}

    /**
     * @param string         $cardNumber
     * @param User           $curUser
     * @param ManzanaReferal $item
     */
    private function setDataByNoneExistManzanaItemInSite(string $cardNumber, User $curUser, ManzanaReferal $item): void
    {
        $data = [
            'UF_CARD'    => $cardNumber,
            'UF_USER_ID' => $curUser->getId(),
        ];
        try {
            $skip = false;
            $card = null;
            try {
                $card = $this->manzanaService->searchCardByNumber($cardNumber);
            } catch (CardNotFoundException $e) {
                $skip = true;
            } catch (\Exception $e) {
                $skip = true;
                $this->logger->critical('Ошибка манзаны - ' . $e->getMessage());
            }
            if (!$skip) {
                if (!empty($card->phone)) {
                    try {
                        $phone = PhoneHelper::normalizePhone((string)$card->phone);
                    } catch (WrongPhoneNumberException $e) {
                        $phone = '';
                    }
                } else {
                    $phone = '';
                }
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $data = array_merge(
                    $data,
                    [
                        'UF_NAME'        => (string)$card->firstName,
                        'UF_LAST_NAME'   => (string)$card->lastName,
                        'UF_SECOND_NAME' => (string)$card->secondName,
                        'UF_EMAIL'       => (string)$card->email,
                        'UF_PHONE'       => $phone,
                        'UF_MODERATED'   => $item->isModerated() ? 'Y' : 'N',
                    ]
                );
                try {
                    if ($this->add($data)) {
                        $this->haveAdd = true;
                    }
                } catch (BitrixRuntimeException $e) {
                    $this->logger->error('Ошибка добавления реферрала - ' . $e->getMessage());
                } catch (\Exception $e) {
                    $this->logger->error('Ошибка добавления реферрала - ' . $e->getMessage());
                }
            }
        } catch (ManzanaServiceException $e) {
            $this->logger->critical('Ошибка манзаны - ' . $e->getMessage());
            /** скипаем при ошибке манзаны */
        }
    }

    /**
     * @param Referral       $referral
     * @param ManzanaReferal $item
     *
     * @return Referral
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     */
    private function setDataByExistManzanaItemInSite(Referral $referral, ManzanaReferal $item): Referral
    {
        $cardDate = '';

        $referral->setBonus((float)$item->sumReferralBonus);
        $lastModerate = $referral->isModerate();
        $lastCancelModerate = $referral->isCancelModerate();
        $referral->setModerate($item->isModerated());
        $referral->setCancelModerate($item->isCancelModerate());

        if (!empty($cardDate) || $lastModerate !== $referral->isModerate()
            || $lastCancelModerate !== $referral->isCancelModerate()) {
            $data = [
                'ID'         => $referral->getId(),
                'UF_CARD'    => $referral->getCard(),
                'UF_USER_ID' => $referral->getUserId(),
            ];

            $isCancelModerate = false;
            /** @noinspection NotOptimalIfConditionsInspection */
            if ($lastCancelModerate !== $referral->isCancelModerate()) {
                $data['UF_CANCEL_MODERATE'] = $referral->isCancelModerate() ? 'Y' : 'N';
                if ($data['UF_CANCEL_MODERATE'] === 'Y') {
                    $isCancelModerate = true;
                    if ($data['UF_MODERATED'] === 'Y') {
                        $data['UF_MODERATED'] = 'N';
                    }
                }
            }
            /** @noinspection NotOptimalIfConditionsInspection */
            if ($lastModerate !== $referral->isModerate()) {
                $data['UF_MODERATED'] = $referral->isModerate() ? 'Y' : 'N';
                if ($data['UF_MODERATED'] === 'Y' && $data['UF_CANCEL_MODERATE'] === 'Y') {
                    $data['UF_CANCEL_MODERATE'] = 'N';
                }
            }
            if (\count($data) > 3) {
                /** обновляем сущность полностью, чтобы данные не пропадали */
                $updateData = $this->referralRepository->entityToData($referral);
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $updateData = array_merge($updateData, $data);
                if ($this->update($updateData)) {
                    TaggedCacheHelper::clearManagedCache([
                        'personal:referral:' . $referral->getUserId(),
                    ]);

                    if ($isCancelModerate) {
                        /** если произошла отмена модерации то отправляем письмо или смс */
                        $container = App::getInstance()->getContainer();
                        $userService = $container->get(CurrentUserProviderInterface::class);
                        $user = $userService->getUserRepository()
                            ->find($referral->getUserId());
                        if ($user !== null) {
                            if ($user->hasEmail()) {
                                Event::send(
                                    [
                                        'EVENT_NAME' => 'ReferralModeratedCancel',
                                        'LID'        => SITE_ID,
                                        'C_FIELDS'   => [
                                            'CARD'  => $referral->getCard(),
                                            'EMAIL' => $user->getEmail(),
                                        ],
                                    ]
                                );
                            } elseif ($user->hasPhone()) {
                                $smsService = $container->get('sms.service');
                                $smsService->sendSms('Реферал с номером карты '
                                    . $referral->getCard()
                                    . ' не прошел модерацию',
                                    $user->getNormalizePersonalPhone());
                            }
                        }
                    }
                }
            }
        }

        return $referral;
    }
}
