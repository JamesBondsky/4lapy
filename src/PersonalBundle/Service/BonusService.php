<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
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
use FourPaws\PersonalBundle\Entity\CardBonus;
use FourPaws\PersonalBundle\Entity\UserBonus;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
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
    
    /** @var LoggerInterface */
    private $logger;
    
    /**
     * ReferralService constructor.
     *
     * @param ManzanaService $manzanaService
     *
     * @throws \RuntimeException
     * @throws ServiceNotFoundException
     */
    public function __construct(ManzanaService $manzanaService)
    {
        $this->manzanaService = $manzanaService;
        $this->logger         = LoggerFactory::create('manzana');
    }
    
    /**
     * @return UserBonus
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws NotAuthorizedException
     */
    public function getCurUserBonusInfo() : UserBonus
    {
        $bonus = new UserBonus();
        $bonus->setEmpty(true);
        try {
            /** @var Contact $contact */
            $contact = $this->manzanaService->getContactByCurUser();
            
            if ($contact->isLoyaltyProgramContact()) {
                /** @var ArrayCollection $cards */
                $cards = $contact->cards;
                if (!$cards->isEmpty()) {
                    /** @var CardByContractCards $card */
                    $cardBonus = new CardBonus();
                    $cardBonus->setEmpty(true);
                    foreach ($cards as $userCard) {
                        $card = $this->manzanaService->getCardInfo($userCard->cardNumber, $contact->contactId);
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
            }
        } catch (ManzanaServiceContactSearchMoreOneException $e) {
            /** @var UserService $userService */
            $userService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
            $phone       = $userService->getCurrentUser()->getPersonalPhone();
            $this->logger->info('Найдено больше одного пользователя в манзане по телефону ' . $phone);
        } catch (ManzanaServiceContactSearchNullException $e) {
            /** @var UserService $userService */
            $userService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
            $phone       = $userService->getCurrentUser()->getPersonalPhone();
            $this->logger->info('Не найдено пользователей в манзане по телефону ' . $phone);
        } /** сбрасываем исключения связанные с ошибкой сервиса и возвращаем пустой объект */
        catch (ManzanaServiceException $e) {
        }
        
        return $bonus;
    }
    
    /**
     * @param string $bonusCard
     *
     * @return bool
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws ApplicationCreateException
     * @throws ConstraintDefinitionException
     * @throws ServiceCircularReferenceException
     * @throws NotAuthorizedException
     */
    public function activateBonusCard(string $bonusCard) : bool
    {
        $contact             = new Client();
        $contact->cardnumber = $bonusCard;
        try {
            $contact->contactId = $this->manzanaService->getContactByCurUser();
            $this->manzanaService->updateContact($contact);
            
            return true;
            /** сбрасываем исключения связанные с маназной если не найден пользователь или ошибка сервиса и возвращаем пустой объект */
        } catch (ManzanaServiceContactSearchMoreOneException $e) {
            /** @var UserService $userService */
            $userService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
            $this->logger->info(
                'Найдено больше одного пользователя в манзане по телефону ' . $userService->getCurrentUser()
                                                                                          ->getPersonalPhone()
            );
        } catch (ManzanaServiceContactSearchNullException $e) {
            /** @var UserService $userService */
            $userService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
            $this->logger->info(
                'Не найдено пользователей в манзане по телефону ' . $userService->getCurrentUser()->getPersonalPhone()
            );
        } /** глушим остальные ошибки по манзане и обрабытываем в контроллере - финальный return */
        catch (ManzanaServiceException $e) {
        } catch (ManzanaException $e) {
        }
        
        return false;
    }
}
