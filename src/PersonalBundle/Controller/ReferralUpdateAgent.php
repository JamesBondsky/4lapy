<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Controller;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\External\Manzana\Model\Card_by_contract_Cards;
use FourPaws\External\Manzana\Model\Referral as ManzanaReferral;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\PersonalBundle\Service\ReferralService;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ReferralUpdateAgent
 *
 * @package FourPaws\PersonalBundle\Controller
 */
class ReferralUpdateAgent
{
    /**
     * @throws ValidationException
     * @throws ConstraintDefinitionException
     * @throws BitrixRuntimeException
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return string
     */
    public static function updateModerateReferrals() : string
    {
        /** @var ReferralService $referralService */
        $referralService = App::getInstance()->getContainer()->get('referral.service');
        $referrals       = $referralService->referralRepository->findBy(['filter' => ['UF_MODERATED' => 1]]);
        if (\is_array($referrals) && !empty($referrals)) {
            $manzanaReferrals = [];
            foreach ($referrals as $referral) {
                $userId = $referral->getUserId();
                if (!array_key_exists($userId, $manzanaReferrals)) {
                    try {
                        $user                      =
                            $referralService->referralRepository->curUserService->getUserRepository()->find(
                                $userId
                            );
                        $manzanaReferrals[$userId] = $referralService->manzanaService->getUserReferralList(
                            $user
                        );
                    } catch (ManzanaServiceContactSearchMoreOneException $e) {
                    } catch (ManzanaServiceContactSearchNullException $e) {
                    } catch (ManzanaServiceException $e) {
                    } catch (NotAuthorizedException $e) {
                    }
                }
                if (\is_array($manzanaReferrals[$userId])
                    && !empty($manzanaReferrals[$userId])) {
                    /** @var ManzanaReferral $manzanaReferral */
                    /** @noinspection ForeachSourceInspection */
                    foreach ($manzanaReferrals[$userId] as $manzanaReferral) {
                        if ($manzanaReferral->cardNumber === $referral->getCard()) {
                            if ($manzanaReferral->isQuestionnaireActual === 'Да') {
                                try {
                                    $card = $referralService->manzanaService->searchCardByNumber(
                                        $manzanaReferral->cardNumber
                                    );
                                    try {
                                        $cardInfo = $referralService->manzanaService->getCardInfo(
                                            $manzanaReferral->cardNumber,
                                            $card->contactId
                                        );
                                        try {
                                            $phone = PhoneHelper::normalizePhone($card->phone);
                                        } catch (WrongPhoneNumberException $e) {
                                            $phone = '';
                                        }
                                        $data = [
                                            'ID'                  => $referral->getId(),
                                            'UF_NAME'             => $card->firstName,
                                            'UF_LAST_NAME'        => $card->lastName,
                                            'UF_SECOND_NAME'      => $card->secondName,
                                            'UF_EMAIL'            => $card->email,
                                            'UF_PHONE'            => $phone,
                                            'UF_CARD'             => $manzanaReferral->cardNumber,
                                            'UF_CARD_CLOSED_DATE' => $cardInfo instanceof
                                                                     Card_by_contract_Cards ? $cardInfo->getExpireDate()
                                                                                                       ->format(
                                                                                                           'd.m.Y'
                                                                                                       ) : '',
                                            'UF_MODERATED'        => 'N',
                                        ];
                                        $referralService->update($data);
                                    } catch (ManzanaServiceException $e) {
                                    }
                                } catch (ManzanaServiceException $e) {
                                } catch (CardNotFoundException $e) {
                                }
                            }
                        }
                    }
                    
                }
            }
            
        }
        
        return '\\' . __METHOD__ . '();';
    }
}
