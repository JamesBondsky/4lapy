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
     *
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
    public function getCurUserReferrals(PageNavigation $nav = null): array
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
                    $filter['<=UF_CARD_CLOSED_DATE'] = new Date();
                    $filter['UF_MODERATED'] = 0;
                    break;
                case 'moderated':
                    $filter['UF_MODERATED'] = 1;
                    break;
            }
        }
        if ($nav instanceof PageNavigation) {
            $this->referralRepository->setNav($nav);
        }
        $curUser = $this->referralRepository->curUserService->getCurrentUser();
        if (!empty($filter)) {
            $filter['UF_USER_ID'] = $curUser->getId();
            $referrals = $this->referralRepository->findBy(
                [
                    'filter' => $filter,
                    'ttl'    => 360000,
                ]
            );
        } else {
            $referrals = $this->referralRepository->findByCurUser();
        }
        if ($nav !== null) {
            $nav = $this->referralRepository->getNav();
            $this->referralRepository->clearNav();
        }

        [, $haveAdd, $referrals] = $this->setDataByManzana($curUser, $referrals);

        return [$referrals, $haveAdd];
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
                    'UF_MODERATED'         => 0,
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
                    'UF_USER_ID'   => $this->referralRepository->curUserService->getCurrentUserId(),
                    'UF_MODERATED' => 1,
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
        return $this->referralRepository->findBy(['filter' => ['UF_MODERATED' => 1]]);
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

    /**
     * @param User            $curUser
     * @param ArrayCollection $referrals
     *
     * @return array
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     * @throws \Exception
     */
    private function setDataByManzana(
        User $curUser,
        ArrayCollection $referrals
    ): array {
        $arCards = [];
        if (!$referrals->isEmpty()) {
            /** @var Referral $item */
            foreach ($referrals as $key => $item) {
                if (!empty($item->getCard())) {
                    $arCards[$item->getCard()] = $key;
                }
            }
        }
        $referralsList = $referrals->toArray();

        $manzanaReferrals = [];
        try {
            $manzanaReferrals = $this->manzanaService->getUserReferralList($curUser);
        } catch (ManzanaServiceException $e) {
            $this->logger->critical('Ошибка манзаны - ' . $e->getMessage());
        } catch (NotAuthorizedException $e) {
            /** прерываем выполнение если неавторизованы */
            return [false, false];
        }
        $haveAdd = false;
        if (\is_array($manzanaReferrals) && !empty($manzanaReferrals)) {
            /** @var ManzanaReferal $item */
            foreach ($manzanaReferrals as $item) {
                $item->cardNumber = (string)$item->cardNumber;
                if (empty($item->cardNumber)) {
                    continue;
                }
                if (!\array_key_exists($item->cardNumber, $arCards)) {
                    $data = [
                        'UF_CARD'    => $item->cardNumber,
                        'UF_USER_ID' => $curUser->getId(),
                    ];
                    try {
                        $skip = false;
                        $card = null;
                        try {
                            $card = $this->manzanaService->searchCardByNumber($item->cardNumber);
                        } catch (CardNotFoundException $e) {
                            $skip = true;
                        } catch (\Exception $e) {
                            $this->logger->critical('Ошибка манзаны - ' . $e->getMessage());
                        }
                        if (!$skip) {
                            $cardInfo = null;
                            if (!empty(!empty($card->contactId))) {
                                $cardInfo = $this->manzanaService->getCardInfo($item->cardNumber, $card->contactId);
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
                                    'UF_MODERATED'        => $item->isQuestionnaireActual === 'Не указано' ? 'Y' : 'N',
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
                } /** @var Referral $referral */
                else {
                    $referral =& $referralsList[$arCards[$item->cardNumber]];
                    if ($referral instanceof Referral) {
                        $referral->setBonus((float)$item->sumReferralBonus);
                        $lastModerate = $referral->isModerate();
                        $referral->setModerate($item->isQuestionnaireActual === 'Не указано');
                        if ($lastModerate !== $referral->isModerate()) {
                            $this->update(
                                [
                                    'ID'           => $referral->getId(),
                                    'UF_MODERATED' => $referral->isModerate() ? 'Y' : 'N',
                                ]
                            );
                        }
                    }
                }
                unset($referral);
            }
        }
        $referrals = new ArrayCollection($referralsList);
        return [true, $haveAdd, $referrals];
    }
}
