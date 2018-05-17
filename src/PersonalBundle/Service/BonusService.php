<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\SystemException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\ManzanaException;
use FourPaws\External\Manzana\Model\CardByContractCards;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\Manzana\Model\Contact;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Entity\CardBonus;
use FourPaws\PersonalBundle\Entity\UserBonus;
use FourPaws\PersonalBundle\Exception\CardNotValidException;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class BonusService
 *
 * @package FourPaws\PersonalBundle\Service
 */
class BonusService
{

    /**
     * @var ManzanaService
     */
    public $manzanaService;

    /**
     * @var CurrentUserProviderInterface
     */
    public $currentUserProvider;

    /** @var LoggerInterface */
    private $logger;

    /**
     * ReferralService constructor.
     *
     * @param ManzanaService               $manzanaService
     * @param CurrentUserProviderInterface $currentUserProvider
     *
     * @throws \RuntimeException
     * @throws ServiceNotFoundException
     */
    public function __construct(ManzanaService $manzanaService, CurrentUserProviderInterface $currentUserProvider)
    {
        $this->manzanaService = $manzanaService;
        $this->currentUserProvider = $currentUserProvider;
        $this->logger = LoggerFactory::create('manzana');
    }

    /**
     * @param User $user
     *
     * @return UserBonus
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws NotAuthorizedException
     */
    public function getUserBonusInfo(User $user = null): UserBonus
    {
        if ($user === null) {
            $user = $this->currentUserProvider->getCurrentUser();
        }

        $bonus = new UserBonus();
        $bonus->setEmpty(true);
        try {
            $bonus = static::getManzanaBonusInfo($user, $this->manzanaService);
        } catch (ManzanaServiceContactSearchMoreOneException $e) {
            $this->logger->info(
                'Найдено больше одного пользователя в манзане по телефону ' . $user->getPersonalPhone()
            );
        } catch (ManzanaServiceContactSearchNullException $e) {
            $this->logger->info('Не найдено пользователей в манзане по телефону ' . $user->getPersonalPhone());
        } /** сбрасываем исключения связанные с ошибкой сервиса и возвращаем пустой объект */
        catch (ManzanaServiceException $e) {
            $this->logger->error('Ошибка манзаны - '.$e->getMessage());
        }

        return $bonus;
    }

    /**
     * @param User $user
     * @param null|ManzanaService $manzanaService
     *
     * @return UserBonus
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws ManzanaServiceContactSearchMoreOneException
     * @throws ManzanaServiceContactSearchNullException
     * @throws ManzanaServiceException
     */
    public static function getManzanaBonusInfo(User $user, ?ManzanaService $manzanaService = null): UserBonus
    {
        $bonus = new UserBonus();
        $bonus->setEmpty(true);

        if(!($manzanaService instanceof ManzanaService)){
            $manzanaService = App::getInstance()->getContainer()->get('manzana.service');
        }

        if(!($manzanaService instanceof ManzanaService)){
            throw new ManzanaServiceException('хрень - объект не установлен');
        }

        if(empty($user->getPersonalPhone())){
            throw new ManzanaServiceException('телефона нет - выполнить запрос нельзя');
        }

        /** @var Contact $contact */
        if(!empty($user->getPersonalPhone())) {
            $contact = $manzanaService->getContactByUser($user);
        }
        else{
            $contact = null;
        }

        if ($contact instanceof Client && $contact->isLoyaltyProgramContact()) {
            /** @var CardByContractCards $card */
            $cardBonus = new CardBonus();
            $cardBonus->setEmpty(true);
            if (!empty($user->getDiscountCardNumber())) {
                $card = $manzanaService->getCardInfo($user->getDiscountCardNumber(), $contact->contactId);
                if ($card !== null && $card->isActive()) {
                    $cardBonus->setCardId($card->cardId);
                    $cardBonus->setCardNumber($card->cardNumber);
                    $cardBonus->setSum((float)$card->sum);
                    $cardBonus->setDebit((float)$card->debit);
                    $cardBonus->setCredit((float)$card->credit);
                    $cardBonus->setActiveBalance((float)$card->activeBalance);
                    $cardBonus->setBalance((float)$card->balance);
                    $cardBonus->setDiscount((float)$card->discount);
                    $cardBonus->setReal((int)substr($card->cardNumber, 0, 2) === 26);
                    $cardBonus->setEmpty(false);
                }
            } else {
                /** @var ArrayCollection $cards */
                $cards = $contact->cards;
                if (!$cards->isEmpty()) {
                    foreach ($cards as $userCard) {
                        $card = $manzanaService->getCardInfo($userCard->cardNumber, $contact->contactId);
                        if ($card !== null && $card->isActive()) {
                            $cardBonus->setCardId($card->cardId);
                            $cardBonus->setCardNumber($card->cardNumber);
                            $cardBonus->setSum((float)$card->sum);
                            $cardBonus->setDebit((float)$card->debit);
                            $cardBonus->setCredit((float)$card->credit);
                            $cardBonus->setActiveBalance((float)$card->activeBalance);
                            $cardBonus->setBalance((float)$card->balance);
                            $cardBonus->setDiscount((float)$card->discount);
                            $cardBonus->setReal((int)substr($card->cardNumber, 0, 2) === 26);
                            $cardBonus->setEmpty(false);
                            break;
                        }
                    }

                }
            }
            if (!$cardBonus->isEmpty()) {
                $bonus->setActiveBonus((float)$contact->plActiveBalance);
                $bonus->setAllBonus((float)$contact->plBalance);
                $bonus->setCredit((float)$contact->plCredit);
                $bonus->setDebit((float)$contact->plDebet);
                $bonus->setSum((float)$contact->plSumm);
                $bonus->setDiscount((float)$contact->plDiscount);

                $bonus->setCard($cardBonus);

                $bonus->setEmpty(false);
            }
        }
        return $bonus;
    }

    /**
     * @param string    $bonusCard
     * @param User|null $user
     *
     * @return bool
     *
     * @throws SystemException
     * @throws BitrixRuntimeException
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws ApplicationCreateException
     * @throws ConstraintDefinitionException
     * @throws ServiceCircularReferenceException
     * @throws NotAuthorizedException
     * @throws CardNotValidException
     * @throws ManzanaServiceException
     */
    public function activateBonusCard(string $bonusCard, User $user = null): bool
    {
        if (!($user instanceof User)) {
            $user = $this->currentUserProvider->getCurrentUser();
        }

        if(empty($user->getPersonalPhone())){
            throw new ManzanaServiceException('телефона нет - выполнить запрос нельзя');
        }

        $validCardResult = $this->manzanaService->validateCardByNumberRaw($bonusCard);
        if (!$validCardResult->isValid) {
            throw new CardNotValidException('Карта не привязывается');
        }

        $bonusCardId = $validCardResult->cardId;

        $contact = new Client();
        $contact->cardnumber = $bonusCard;
        /** для регистрации из ЛК покупателя */
        $contact->shopRegistration = 'Ishop';
        $contact->shopOfActivation = 'UpdatedByСlient';

        try {
            $client = $this->manzanaService->getContactByUser($user);

            if ($client instanceof Client) {
                $contact->contactId = $client->contactId;
            } else {
                throw new ManzanaServiceException('Контакт не найден');
            }

            /** @var ArrayCollection $cards */
            $oldCardId = '';
            $cards = $client->cards;
            if (!$cards->isEmpty()) {
                foreach ($cards as $userCard) {
                    $card = $this->manzanaService->getCardInfo($userCard->cardNumber, $client->contactId);
                    if ($card !== null && $card->isActive()) {
                        $oldCardId = $card->cardId;
                        break;
                    }
                }
            }

            $isChange = false;
            if(!empty($oldCardId) && !empty($bonusCardId)) {
                $isChange = $this->manzanaService->changeCard($oldCardId, $bonusCardId);
            }
            elseif (empty($oldCardId)){
                $this->manzanaService->updateContact($contact);
                $isChange = true;
            }

            if($isChange) {
                $this->currentUserProvider->getUserRepository()->updateDiscountCard($user->getId(), $bonusCard);

                TaggedCacheHelper::clearManagedCache([
                    'personal:bonus:' . $user->getId(),
                ]);

            }

            return $isChange;
            /** сбрасываем исключения связанные с маназной если не найден пользователь или ошибка сервиса и возвращаем пустой объект */
        } catch (ManzanaServiceContactSearchMoreOneException $e) {
            $this->logger->info(
                'Найдено больше одного пользователя в манзане по телефону ' . $user->getPersonalPhone()
            );
        } catch (ManzanaServiceContactSearchNullException $e) {
            $this->logger->info(
                'Не найдено пользователей в манзане по телефону ' . $user->getPersonalPhone()
            );
        } catch (ManzanaServiceException|ManzanaException $e) {
            $this->logger->error('Ошибка манзаны - '.$e->getMessage());
            /** глушим остальные ошибки по манзане и обрабытываем в контроллере - финальный return */
        }

        return false;
    }
}
