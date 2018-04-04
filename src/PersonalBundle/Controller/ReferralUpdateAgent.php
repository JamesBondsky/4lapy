<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Controller;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Mail\Event;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\External\Manzana\Model\CardByContractCards;
use FourPaws\External\Manzana\Model\Referral as ManzanaReferral;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Service\ReferralService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
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
     * @return string
     */
    public static function updateModerateReferrals(): string
    {
        $loggerManzana = LoggerFactory::create('manzana');
        $loggerReferal = LoggerFactory::create('referal');
        $loggerSystem = LoggerFactory::create('system');
        $loggerParams = LoggerFactory::create('params');
        /** @var ReferralService $referralService */
        try {
            $container = App::getInstance()->getContainer();
            $referralService = $container->get('referral.service');
            $referrals = $referralService->getModeratedReferrals();
        } catch (ApplicationCreateException $e) {
            $referrals = new ArrayCollection();
        } catch (\Exception $e) {
            $referrals = new ArrayCollection();
        }
        if (!$referrals->isEmpty()) {
            $manzanaReferrals = [];
            foreach ($referrals as $referral) {
                $userId = $referral->getUserId();
                if (!array_key_exists($userId, $manzanaReferrals)) {
                    try {
                        $user = $referralService->referralRepository->curUserService->getUserRepository()->find($userId);
                        $manzanaReferrals[$userId] = $referralService->manzanaService->getUserReferralList($user);
                    } catch (ManzanaServiceContactSearchMoreOneException $e) {
                        $loggerManzana->warning('Найдено больше 1 юзера');
                        /** глушим так как продолжения все равно нет, а фатал делать нельзя */
                    } catch (ManzanaServiceContactSearchNullException $e) {
                        $loggerManzana->warning('не найдено таких пользователей в манзане');
                        /** глушим так как продолжения все равно нет, а фатал делать нельзя */
                    } catch (ManzanaServiceException $e) {
                        $loggerManzana->error('манзана не работает - ' . $e->getMessage());
                        /** глушим так как продолжения все равно нет, а фатал делать нельзя */
                    } catch (NotAuthorizedException $e) {
                        /** эксепшн никогда не выбьется */
                    } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException $e) {
                        $loggerSystem->error('Ошибка загрузки сервисов');
                    } catch (ConstraintDefinitionException|InvalidIdentifierException $e) {
                        $loggerParams->error('Ошибка параметров');
                    }
                }
                if (\is_array($manzanaReferrals[$userId])
                    && !empty($manzanaReferrals[$userId])) {
                    /** @var ManzanaReferral $manzanaReferral */
                    /** @noinspection ForeachSourceInspection */
                    foreach ($manzanaReferrals[$userId] as $manzanaReferral) {
                        if ($manzanaReferral->cardNumber === $referral->getCard()) {
                            if ($manzanaReferral->isSuccessModerate()) {
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
                                            'UF_CARD'             => $manzanaReferral->cardNumber,
                                            'UF_CARD_CLOSED_DATE' => $cardInfo instanceof
                                            CardByContractCards ? $cardInfo->getExpireDate()
                                                ->format(
                                                    'd.m.Y'
                                                ) : '',
                                            'UF_MODERATED'        => 'N',
                                            'UF_CANCEL_MODERATE'  => 'N',
                                        ];
                                        if ($card->firstName) {
                                            $data['UF_NAME'] = $card->firstName;
                                        }
                                        if ($card->lastName) {
                                            $data['UF_LAST_NAME'] = $card->lastName;
                                        }
                                        if ($card->secondName) {
                                            $data['UF_SECOND_NAME'] = $card->secondName;
                                        }
                                        if ($card->email) {
                                            $data['UF_EMAIL'] = $card->email;
                                        }
                                        if ($phone) {
                                            $data['UF_PHONE'] = $phone;
                                        }
                                        /** обновляем сущность полностью, чтобы данные не пропадали */
                                        $updateData = $referralService->referralRepository->entityToData($referral);
                                        /** @noinspection SlowArrayOperationsInLoopInspection */
                                        $updateData = array_merge($updateData, $data);
                                        if ($referralService->update($updateData)) {
                                            TaggedCacheHelper::clearManagedCache(['personal:referral:' . $data['UF_USER_ID']]);
                                        }
                                    } catch (ManzanaServiceException $e) {
                                        $loggerManzana->error('манзана не работает - ' . $e->getMessage());
                                        /** Если манзана недоступна просто не будет обновления */
                                    } catch (EmptyEntityClass $e) {
                                        /** не вознкнет - всегда передается массив данных */
                                    } catch (\Exception $e) {
                                        $loggerReferal->error('При обновлении возникла ошибка - ' . $e->getMessage());
                                        /** Если манзана недоступна просто не будет обновления */
                                    }
                                } catch (ManzanaServiceException $e) {
                                    $loggerManzana->error('манзана не работает - ' . $e->getMessage());
                                    /** Если манзана недоступна просто не будет обновления */
                                } catch (CardNotFoundException $e) {
                                    /** Если не нашли такой карты в манзане то удалим из сайта */
                                    try {
                                        $referralService->delete($referral->getId(), $referral->getUserId());
                                    } catch (\Exception $e) {
                                        $loggerSystem->error('произошла ошибка удаления реферала ' . $e->getMessage());
                                    }
                                }
                            } elseif (!$manzanaReferral->isSuccessModerate() && !$manzanaReferral->isModerated()) {
                                /** ставим пометку что модерация отменена */
                                /** обновляем сущность полностью, чтобы данные не пропадали */
                                $updateData = $referralService->referralRepository->entityToData($referral);
                                $data = ['UF_CANCEL_MODERATE' => 'Y', 'UF_MODERATED' => 'N'];
                                /** @noinspection SlowArrayOperationsInLoopInspection */
                                $updateData = array_merge($updateData, $data);
                                if ($referralService->update($updateData)) {
                                    TaggedCacheHelper::clearManagedCache(['personal:referral:' . $data['UF_USER_ID']]);
                                    $userService = $container->get(CurrentUserProviderInterface::class);
                                    $user = $userService->getUserRepository()->find($referral->getUserId());
                                    if($user !== null) {
                                        if ($user->hasEmail()) {
                                            Event::send(
                                                [
                                                    'EVENT_NAME' => 'ReferralModeratedCancel',
                                                    'LID'        => SITE_ID,
                                                    'C_FIELDS'   => [
                                                        'CARD'       => $referral->getCard(),
                                                        'EMAIL' => $user->getEmail(),
                                                    ],
                                                ]
                                            );
                                        } elseif (!empty($user->getPersonalPhone())) {
                                            $smsService = $container->get('sms.service');
                                            $smsService->sendSms('Реферал с номером карты '.$referral->getCard().' не прошел модерацию', $user->getNormalizePersonalPhone());
                                        }
                                    }
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
