<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Service;

use Bitrix\Main\Application;
use Bitrix\Main\Type\Date;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Model\Card_by_contract_Cards;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\Manzana\Model\Referral as ManzanaReferal;
use FourPaws\External\Manzana\Model\ReferralParams as ManzanaReferalParams;
use FourPaws\External\ManzanaService;
use FourPaws\PersonalBundle\Entity\Referral;
use FourPaws\PersonalBundle\Repository\ReferralRepository;
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
    private $referralRepository;
    
    /**
     * @var ManzanaService
     */
    private $manzanaService;
    
    /**
     * ReferralService constructor.
     *
     * @param ReferralRepository $referralRepository
     * @param ManzanaService     $manzanaService
     */
    public function __construct(ReferralRepository $referralRepository, ManzanaService $manzanaService)
    {
        $this->referralRepository = $referralRepository;
        $this->manzanaService     = $manzanaService;
    }
    
    /**
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @throws ManzanaServiceException
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     * @return Referral[]|array
     */
    public function getCurUserReferrals() : array
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $request      = Application::getInstance()->getContext()->getRequest();
        $search       = (string)$request->get('search');
        $referralType = (string)$request->get('referral_type');
        $filter       = [];
        if (!empty($search)) {
            $filter['=UF_CARD'] = $search;
        }
        if (!empty($referralType)) {
            switch ($referralType) {
                case 'active':
                    /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                    $filter['<=UF_CARD_CLOSED_DATE'] = new Date();
                    break;
                case 'moderated':
                    $filter['UF_MODERATED'] = 1;
                    break;
            }
        }
        $arCards        = [];
        $curUserService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
        $curUser        = $curUserService->getCurrentUser();
        if (!empty($filter)) {
            $filter['UF_USER_ID'] = $curUser->getId();
            $referrals            = $this->referralRepository->findBy(
                [
                    'filter' => $filter,
                    'ttl'    => 360000,
                ]
            );
        } else {
            $referrals = $this->referralRepository->findByCurUser();
        }
        if (\is_array($referrals) && !empty($referrals)) {
            /** @var Referral $item */
            foreach ($referrals as $key => $item) {
                $arCards[$item->getCard()] = $key;
            }
        }
        
        $manzanaReferrals = $this->manzanaService->getUserReferralList($curUser);
        if (\is_array($manzanaReferrals) && !empty($manzanaReferrals)) {
            /** @var ManzanaReferal $item */
            $haveAdd = false;
            foreach ($manzanaReferrals as $item) {
                if (!empty($arCards) && !array_key_exists($item->cardNumber, $arCards)) {
                    $data = [
                        'UF_CARD'    => $item->cardNumber,
                        'UF_USER_ID' => $curUser->getId(),
                    ];
                    try {
                        $card     = $this->manzanaService->searchCardByNumber($item->cardNumber);
                        $cardInfo = $this->manzanaService->getCardInfo($item->cardNumber, $card->contactId);
                        $data     = [
                            'UF_NAME'             => $card->firstName,
                            'UF_LAST_NAME'        => $card->lastName,
                            'UF_SECOND_NAME'      => $card->secondName,
                            'UF_EMAIL'            => $card->email,
                            'UF_PHONE'            => $card->phone,
                            'UF_CARD'             => $item->cardNumber,
                            'UF_USER_ID'          => $curUser->getId(),
                            'UF_CARD_CLOSED_DATE' => $cardInfo instanceof
                                                     Card_by_contract_Cards ? $cardInfo->getFormatExpireDate() : '',
                            'UF_MODERATED'        => $item->isQuestionnaireActual !== 'Да' ? 'Y' : 'N',
                        ];
                    } catch (ManzanaServiceException $e) {
                    } catch (CardNotFoundException $e) {
                    }
                    try {
                        $this->add($data);
                        if (!$haveAdd) {
                            $haveAdd = true;
                        }
                    } catch (BitrixRuntimeException $e) {
                    } catch (\Exception $e) {
                    }
                }
                /** @var Referral $referral */
                $referral =& $referrals[$arCards[$item->cardNumber]];
                $referral->setBonus((float)$item->sumReferralBonus);
                $lastModerate = $referral->isModerate();
                $referral->setModerate($item->isQuestionnaireActual !== 'Да');
                if ($lastModerate !== $referral->isModerate()) {
                    $this->update(['UF_MODERATED' => $referral->isModerate() ? 'Y' : 'N']);
                }
            }
            if ($haveAdd) {
                /** обновляем если добавилась инфа, чтобы была актуальная постраничка, табы и поиск */
                LocalRedirect($request->getRequestUri());
            }
            
        }
        
        return $referrals;
    }
    
    /**
     * @param array $data
     *
     * @param bool  $updateManzana
     *
     * @return bool
     * @throws ValidationException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws \Exception
     * @throws BitrixRuntimeException
     */
    public function add(array $data, bool $updateManzana = true) : bool
    {
        /** @var Referral $entity */
        $entity = $this->referralRepository->dataToEntity($data, Referral::class);
        $res    = $this->referralRepository->setEntity($entity)->create();
        if ($res && $updateManzana) {
            $referralClient = $this->getClientReferral($entity);
            if (!empty($referralClient->contactId) && !empty($referralClient->cardNumber)) {
                $this->manzanaService->addReferralByBonusCard($referralClient);
            }
        }
        
        return $res;
    }
    
    /**
     * @param Referral $referral
     *
     * @return ManzanaReferalParams
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public function getClientReferral(Referral $referral) : ManzanaReferalParams
    {
        $client = new ManzanaReferalParams();
        
        $contactId = '';
        try {
            $contactId = $this->manzanaService->getContactIdByCurUser();
        } catch (ManzanaServiceContactSearchMoreOneException $e) {
        } catch (ManzanaServiceContactSearchNullException $e) {
            $contactClient = new Client();
            try {
                App::getInstance()
                   ->getContainer()
                   ->get(CurrentUserProviderInterface::class)
                   ->setClientPersonalDataByCurUser($contactClient);
            } catch (NotAuthorizedException $e) {
            }
            try {
                $res       = $this->manzanaService->updateContact($contactClient);
                $contactId = $res->contactId;
            } catch (ManzanaServiceException $e) {
            } catch (ContactUpdateException $e) {
            }
        } catch (NotAuthorizedException $e) {
        } catch (ManzanaServiceException $e) {
        }
        
        if (!empty($contactId)) {
            $client->contactId  = $contactId;
            $client->cardNumber = $referral->getCard();
            $client->phone      = $referral->getPhone();
            $client->email      = $referral->getEmail();
            $client->lastName   = $referral->getLastName();
            $client->secondName = $referral->getSecondName();
            $client->name       = $referral->getName();
        }
        
        return $client;
    }
    
    /**
     * @param array $data
     *
     * @return bool
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     */
    public function update(array $data) : bool
    {
        return $this->referralRepository->setEntityFromData($data, Referral::class)->update();
    }
}
