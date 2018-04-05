<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\UI\PageNavigation;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Model\CardByContractCards;
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
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

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
        $this->currentUser = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        $this->logger = LoggerFactory::create('referral');
    }

    /**
     * @param PageNavigation|null $nav
     * @param bool                $main
     *
     * @throws \Bitrix\Main\ObjectException
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
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $request = Application::getInstance()->getContext()->getRequest();
        $search = (string)$request->get('search');
        $filter = [];
        if (!empty($search)) {
            $filter['?UF_CARD'] = $search;
        }
        $referralType = $this->getReferralType();
        if (!empty($referralType)) {
            switch ($referralType) {
                case 'active':
                    /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                    /** Дата окончания активности должна быть больше текущей даты */
                    $filter['>=UF_CARD_CLOSED_DATE'] = new Date();
                    $filter['UF_MODERATED'] = [0, null, ''];
                    $filter['UF_CANCEL_MODERATE'] = [0, null, ''];
                    break;
                case 'moderated':
                    $filter['UF_MODERATED'] = 1;
                    $filter['UF_CANCEL_MODERATE'] = [0, null, ''];
                    break;
            }
        }
        if ($nav instanceof PageNavigation) {
            $this->referralRepository->setNav($nav);
        }
        $curUser = $this->referralRepository->curUserService->getCurrentUser();
        if (!empty($filter)) {
            $filter['UF_USER_ID'] = $curUserId = $curUser->getId();
            $cacheTime = 360000;
            try {
                $instance = Application::getInstance();
            } catch (SystemException $e) {
                $logger = LoggerFactory::create('system');
                $logger->error('Ошибка получения инстанса' . $e->getMessage());
            }
            $cache = $instance->getCache();
            $referrals = new ArrayCollection();

            if ($cache->initCache($cacheTime,
                serialize($filter),
                __FUNCTION__ . '\ReferralsByFilter')) {
                $result = $cache->getVars();
                $referrals = $result['referrals'];
            } elseif ($cache->startDataCache()) {
                $tagCache = null;
                if (\defined('BX_COMP_MANAGED_CACHE')) {
                    $tagCache = $instance->getTaggedCache();
                    $tagCache->startTagCache(__FUNCTION__ . '\ReferralsByFilter');
                }

                $referrals = $this->referralRepository->findBy(
                    [
                        'filter' => $filter,
                    ]
                );

                if ($tagCache !== null) {
                    TaggedCacheHelper::addManagedCacheTags([
                        'hlb:field:referral_user:' . $curUserId,
                    ], $tagCache);
                    $tagCache->endTagCache();
                }

                $cache->endDataCache([
                    'referrals' => $referrals,
                ]);
            }
        } else {
            $referrals = $this->referralRepository->findByCurUser();
        }
        if ($nav !== null) {
            $nav = $this->referralRepository->getNav();
            $this->referralRepository->clearNav();
        }

        [, $haveAdd, $referrals, $allBonus] = $this->setDataByManzana($curUser, $referrals, $main,
            $nav->getPageCount() > 1);

        return [$referrals, $haveAdd, $allBonus];
    }

    /**
     * @throws SystemException
     * @return string
     */
    public function getReferralType(): string
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $request = Application::getInstance()->getContext()->getRequest();
        $referralType = (string)$request->get('referral_type');
        $search = (string)$request->get('search');
        if (!empty($search)) {
            $referralType = 'all';
        }

        return $referralType;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param array $data
     *
     * @param bool  $updateManzana
     *
     * @throws EmptyEntityClass
     * @throws ManzanaServiceException
     * @throws ContactUpdateException
     * @throws ValidationException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @return bool
     */
    public function add(array $data, bool $updateManzana = true): bool
    {
        if (empty($data['UF_USER_ID'])) {
            $data['UF_USER_ID'] = $this->currentUser->getCurrentUserId();
        }
        /** @var Referral $entity */
        $entity = $this->referralRepository->dataToEntity($data, Referral::class);
        $res = $this->referralRepository->setEntity($entity)->create();
        if ($res && $updateManzana) {
            $referralClient = $this->getClientReferral($entity);
            if (!empty($referralClient->contactId) && !empty($referralClient->cardNumber)) {
                /** @todo отправка через очередь информации */
                $this->manzanaService->addReferralByBonusCard($referralClient);
            }
            /** @var User $user */
            $user = $this->referralRepository->curUserService->getUserRepository()->find($entity->getUserId());
            if ($user instanceof User) {
                Event::send(
                    [
                        'EVENT_NAME' => 'ReferralAdd',
                        'LID'        => SITE_ID,
                        'C_FIELDS'   => [
                            'CARD'       => $entity->getCard(),
                            'MAIN_PHONE' => tplvar('phone_main'),
                        ],
                    ]
                );
            }
        }

        TaggedCacheHelper::clearManagedCache([
            'personal:referral:' . $entity->getUserId(),
        ]);

        return $res;
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
        } catch (NotAuthorizedException $e) {
        } catch (ManzanaServiceException $e) {
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
        return $this->referralRepository->setEntityFromData($data, Referral::class)->update();
    }

    /**
     * @return int
     */
    public function getAllCountByUser(): int
    {
        try {
            return $this->referralRepository->getCount(
                ['UF_USER_ID' => $this->referralRepository->curUserService->getCurrentUserId()]
            );
        } catch (ObjectPropertyException $e) {
        } catch (NotAuthorizedException $e) {
        }

        return 0;
    }

    /**
     * @return int
     * @throws ObjectException
     */
    public function getActiveCountByUser(): int
    {
        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return $this->referralRepository->getCount(
                [
                    'UF_USER_ID'           => $this->referralRepository->curUserService->getCurrentUserId(),
                    '>UF_CARD_CLOSED_DATE' => new Date(),
                    'UF_MODERATED'         => [0, null, ''],
                    'UF_CANCEL_MODERATE'   => [0, null, ''],
                ]
            );
        } catch (ObjectPropertyException $e) {
        } catch (NotAuthorizedException $e) {
        }

        return 0;
    }

    /**
     * @return int
     */
    public function getModeratedCountByUser(): int
    {
        try {
            return $this->referralRepository->getCount(
                [
                    'UF_USER_ID'         => $this->referralRepository->curUserService->getCurrentUserId(),
                    'UF_MODERATED'       => 1,
                    'UF_CANCEL_MODERATE' => [0, null, ''],
                ]
            );
        } catch (ObjectPropertyException $e) {
        } catch (NotAuthorizedException $e) {
        }

        return 0;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @return ArrayCollection|Referral[]
     * @throws ObjectPropertyException
     */
    public function getModeratedReferrals(): ArrayCollection
    {
        return $this->referralRepository->findBy([
            'filter' => [
                'UF_MODERATED'       => 1,
                'UF_CANCEL_MODERATE' => [0, null, ''],
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
        $haveAdd = false;
        $success = false;
        if (!$referrals->isEmpty()) {
            $referralsList = $referrals->toArray();
        }

        try {
            $manzanaReferrals = $this->manzanaService->getUserReferralList($curUser);
        } catch (ManzanaServiceException $e) {
            /** если нет данных от манзаны то дальнейшее выполнение бесполезно */
            $this->logger->critical('Ошибка манзаны - ' . $e->getMessage());
            return [$success, $haveAdd, new ArrayCollection($referralsList), $allBonus];
        } catch (NotAuthorizedException $e) {
            /** прерываем выполнение если неавторизованы */
            return [$success, $haveAdd, new ArrayCollection($referralsList), $allBonus];
        }

        /** если больше одной страницы грузим всех рефералов, чтобы не было дублирония при добавлении */
        if ($needLoadAllItems) {
            $fullReferralsList = $this->referralRepository->findByCurUser()->toArray();
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

        if ($needLoadAllItems) {
            if (!empty($referralsList)) {
                /** @var Referral $item */
                foreach ($referralsList as $key => $item) {
                    if (!empty($item->getCard())) {
                        $arReferralCards[$item->getCard()] = $key;
                    }
                }
            }
        }
        else{
            $arReferralCards = $arCards;
        }

        if (\is_array($manzanaReferrals) && !empty($manzanaReferrals)) {
            /** @var ManzanaReferal $item */
            foreach ($manzanaReferrals as $item) {
                $cardNumber = $item->cardNumber;
                if (empty($item->cardNumber)) {
                    continue;
                }
                $allBonus += (float)$item->sumReferralBonus;
                if (!\array_key_exists($cardNumber, $arCards)) {
                    if (!$main) {
                        continue;
                    }
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
                            $cardInfo = null;
                            if (!empty($card->contactId)) {
                                $cardInfo = $this->manzanaService->getCardInfo($cardNumber, $card->contactId);
                            }
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
                                    'UF_NAME'             => (string)$card->firstName,
                                    'UF_LAST_NAME'        => (string)$card->lastName,
                                    'UF_SECOND_NAME'      => (string)$card->secondName,
                                    'UF_EMAIL'            => (string)$card->email,
                                    'UF_PHONE'            => $phone,
                                    'UF_CARD_CLOSED_DATE' => $cardInfo instanceof
                                    CardByContractCards ? $cardInfo->getExpireDate()->format(
                                        'd.m.Y'
                                    ) : '',
                                    'UF_MODERATED'        => $item->isModerated() ? 'Y' : 'N',
                                ]
                            );
                            try {
                                if ($this->add($data)) {
                                    $haveAdd = true;
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
                } else {
                    /** @var Referral $referral */
                    $referral = null;
                    if(array_key_exists($cardNumber, $arCards)) {
                        $referral =& $fullReferralsList[$arCards[$cardNumber]];
                    }
                    if ($referral !== null) {
                        $cardDate = '';

                        $referral->setBonus((float)$item->sumReferralBonus);
                        $lastModerate = $referral->isModerate();
                        $lastCancelModerate = $referral->isCancelModerate();
                        $referral->setModerate($item->isModerated());
                        $referral->setCancelModerate($item->isCancelModerate());
                        if ($referral->getDateEndActive() === null) {
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
                                    $cardInfo = null;
                                    if (!empty($card->contactId)) {
                                        $cardInfo = $this->manzanaService->getCardInfo($cardNumber, $card->contactId);
                                        $cardDate = $cardInfo instanceof
                                        CardByContractCards ? $cardInfo->getExpireDate()->format(
                                            'd.m.Y'
                                        ) : '';
                                    }
                                }
                            } catch (ManzanaServiceException $e) {
                                $this->logger->critical('Ошибка манзаны - ' . $e->getMessage());
                                /** скипаем при ошибке манзаны */
                            }
                        }
                        if (!empty($cardDate) || $lastModerate !== $referral->isModerate() || $lastCancelModerate !== $referral->isCancelModerate()) {
                            $data = [
                                'ID'         => $referral->getId(),
                                'UF_CARD'    => $referral->getCard(),
                                'UF_USER_ID' => $referral->getUserId(),
                            ];
                            if (!empty($cardDate)) {
                                $data['UF_CARD_CLOSED_DATE'] = $cardDate;
                            }
                            /** @noinspection NotOptimalIfConditionsInspection */
                            $isCancelModerate = false;
                            if ($lastCancelModerate !== $referral->isCancelModerate()) {
                                $data['UF_CANCEL_MODERATE'] = $referral->isCancelModerate() ? 'Y' : 'N';
                                if ($data['UF_CANCEL_MODERATE'] === 'Y') {
                                    $isCancelModerate = true;
                                    if ($data['UF_MODERATED'] === 'Y') {
                                        $data['UF_MODERATED'] = 'N';
                                    }
                                }
                            }
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
                                    TaggedCacheHelper::clearManagedCache(['personal:referral:' . $referral->getUserId()]);

                                    if ($isCancelModerate) {
                                        /** если произошла отмена модерации то отправляем письмо или смс */
                                        $container = App::getInstance()->getContainer();
                                        $userService = $container->get(CurrentUserProviderInterface::class);
                                        $user = $userService->getUserRepository()->find($referral->getUserId());
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
                                            } elseif (!empty($user->getPersonalPhone())) {
                                                $smsService = $container->get('sms.service');
                                                $smsService->sendSms('Реферал с номером карты ' . $referral->getCard() . ' не прошел модерацию',
                                                    $user->getNormalizePersonalPhone());
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        if(array_key_exists($cardNumber, $arReferralCards)) {
                            $referralsList[$arReferralCards[$cardNumber]] = $referral;
                        }
                    }
                }
                unset($referral);
            }
        }
        $success = true;
        return [$success, $haveAdd, new ArrayCollection($referralsList), $allBonus];
    }
}
