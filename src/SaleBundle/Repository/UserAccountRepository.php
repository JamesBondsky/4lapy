<?php

namespace FourPaws\SaleBundle\Repository;

use FourPaws\SaleBundle\Collection\UserAccountCollection;
use FourPaws\SaleBundle\Entity\UserAccount;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\ValidationException;
use FourPaws\UserBundle\Entity\User;
use CSaleUserAccount;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserAccountRepository
{
    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /** @var CSaleUserAccount */
    protected $saleUserAccount;

    /**
     * UserAccountRepository constructor.
     *
     * @param ArrayTransformerInterface $arrayTransformer
     * @param ValidatorInterface $validator
     */
    public function __construct(ArrayTransformerInterface $arrayTransformer, ValidatorInterface $validator)
    {
        $this->saleUserAccount = new CSaleUserAccount();
        $this->arrayTransformer = $arrayTransformer;
        $this->validator = $validator;
    }

    /**
     * @param User $user
     *
     * @return UserAccount
     * @throws NotFoundException
     */
    public function findByUser(User $user): UserAccount
    {
        $userAccounts = $this->findBy(['USER_ID' => $user->getId()]);
        if ($userAccounts->isEmpty()) {
            throw new NotFoundException('User account not found');
        }

        return $userAccounts->first()->setUser($user);
    }

    /**
     * @param array $filter
     * @param array $order
     * @param array $select
     *
     * @return UserAccountCollection
     */
    public function findBy(array $filter = [], array $order = [], array $select = []): UserAccountCollection
    {
        $result = [];

        $accounts = $this->saleUserAccount->GetList($order, $filter, false, false, $select);
        while ($account = $accounts->Fetch()) {
            $result[] = $account;
        }

        return new UserAccountCollection(
            $this->arrayTransformer->fromArray(
                $result,
                sprintf('array<%s>', UserAccount::class),
                DeserializationContext::create()->setGroups(['read'])
            )
        );
    }

    /**
     * @param UserAccount $userAccount
     *
     * @return bool
     * @throws ValidationException
     */
    public function create(UserAccount $userAccount): bool
    {
        $validationResult = $this->validator->validate($userAccount, null, ['create']);
        if ($validationResult->count() > 0) {
            throw new ValidationException($validationResult);
        }

        return $this->saleUserAccount->Add(
            $this->arrayTransformer->toArray(
                $userAccount,
                SerializationContext::create()->setGroups(['create'])
            )
        );
    }

    /**
     * @param UserAccount $userAccount
     *
     * @return bool
     * @throws ValidationException
     */
    public function update(UserAccount $userAccount): bool
    {
        $validationResult = $this->validator->validate($userAccount, null, ['update']);
        if ($validationResult->count() > 0) {
            throw new ValidationException($validationResult);
        }

        return $this->saleUserAccount->Update(
            $userAccount->getId(),
            SerializationContext::create()->setGroups(['update'])
        );
    }

    public function updateBalance(UserAccount $userAccount): bool
    {
        $validationResult = $this->validator->validate($userAccount, null, ['update']);
        if ($validationResult->count() > 0) {
            throw new ValidationException($validationResult);
        }

        return $this->saleUserAccount->UpdateAccount(
            $userAccount->getId(),
            $userAccount->getCurrentBudget() - $userAccount->getInitialBudget(),
            $userAccount->getCurrency()
        );
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->saleUserAccount->Delete($id);
    }
}
