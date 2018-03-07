<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Service;

use Bitrix\Currency\CurrencyManager;
use FourPaws\PersonalBundle\Service\BonusService;
use FourPaws\SaleBundle\Entity\UserAccount;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\ValidationException;
use FourPaws\SaleBundle\Repository\UserAccountRepository;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;

class UserAccountService
{
    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /** @var BonusService */
    protected $bonusService;

    /**
     * @var UserAccountRepository
     */
    protected $userAccountRepository;

    public function __construct(
        CurrentUserProviderInterface $currentUserProvider,
        BonusService $bonusService,
        UserAccountRepository $userAccountRepository
    ) {
        $this->currentUserProvider = $currentUserProvider;
        $this->bonusService = $bonusService;
        $this->userAccountRepository = $userAccountRepository;
    }

    /**
     * @param null|User $user
     * @param null|float $newBudget
     *
     * @throws ValidationException
     * @return bool
     */
    public function refreshUserBalance(User $user = null, float $newBudget = null): bool
    {
        if (!$user) {
            try {
                $user = $this->currentUserProvider->getCurrentUser();
            } catch (NotAuthorizedException $e) {
                return false;
            }
        }

        if (!$user->getDiscountCardNumber()) {
            return false;
        }

        if (null === $newBudget) {
            $bonus = $this->bonusService->getUserBonusInfo($user);
            if ($bonus->isEmpty()) {
                return false;
            }
            $newBudget = $bonus->getCard()->getBalance();
        }

        try {
            $userAccount = $this->userAccountRepository->findByUser($user);

            return $this->userAccountRepository->updateBalance(
                $userAccount->setCurrentBudget($newBudget)
            );
        } catch (NotFoundException $e) {
        }

        $userAccount = (new UserAccount())->setUser($user)
                                          ->setCurrency(CurrencyManager::getBaseCurrency())
                                          ->setCurrentBudget($newBudget);

        return $this->userAccountRepository->create($userAccount);
    }

    /**
     * @param null|User $user
     *
     * @return UserAccount
     */
    public function findAccountByUser(User $user = null): UserAccount
    {
        if (!$user) {
            $user = $this->currentUserProvider->getCurrentUser();
        }

        return $this->userAccountRepository->findByUser($user);
    }
}
