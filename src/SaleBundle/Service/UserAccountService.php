<?php

namespace FourPaws\SaleBundle\Service;

use Bitrix\Currency\CurrencyManager;
use FourPaws\PersonalBundle\Service\BonusService;
use FourPaws\SaleBundle\Entity\UserAccount;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\ValidationException;
use FourPaws\SaleBundle\Repository\UserAccountRepository;
use FourPaws\UserBundle\Entity\User;
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
     * @param User|null $user
     *
     * @return bool
     * @throws ValidationException
     */
    public function refreshUserBalance(User $user = null): bool
    {
        if (!$user) {
            $user = $this->currentUserProvider->getCurrentUser();
        }

        if (!$user->getDiscountCardNumber()) {
            return false;
        }

        $bonus = $this->bonusService->getUserBonusInfo($user);
        if ($bonus->isEmpty()) {
            return false;
        }

        try {
            $userAccount = $this->userAccountRepository->findByUser($user);

            return $this->userAccountRepository->updateBalance(
                $userAccount->setCurrentBudget($bonus->getCard()->getBalance())
            );
        } catch (NotFoundException $e) {
        }

        $userAccount = (new UserAccount())->setUser($user)
                                          ->setCurrency(CurrencyManager::getBaseCurrency())
                                          ->setCurrentBudget($bonus->getCard()->getActiveBalance());

        return $this->userAccountRepository->create($userAccount);
    }

    /**
     * @param User|null $user
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
