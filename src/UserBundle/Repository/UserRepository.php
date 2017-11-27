<?php

namespace FourPaws\UserBundle\Repository;

use Bitrix\Main\UserTable;
use CUser;
use FourPaws\UserBundle\Entity\User;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserRepository
{
    const FIELD_ID = 'ID';

    /**
     * @var ArrayTransformerInterface
     */
    private $arrayTransformer;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ArrayTransformerInterface $arrayTransformer, ValidatorInterface $validator)
    {
        $this->arrayTransformer = $arrayTransformer;
        $this->cuser = new CUser();
        $this->validator = $validator;
    }


    /**
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        $validationResult = $this->validator->validate($user, null, ['create']);
        if ($validationResult->count() > 0) {
            /**
             * todo change to package exception
             */
            throw new \InvalidArgumentException();
        }

        $result = $this->cuser->Add(
            $this->arrayTransformer->toArray($user, SerializationContext::create()->setGroups(['create']))
        );
        if ((int)$result > 0) {
            $user->setId((int)$result);
            return true;
        }

        /**
         * todo throw exception
         */
        throw new \RuntimeException($this->cuser->LAST_ERROR);
    }

    public function find(int $id)
    {
        $result = $this->findBy([static::FIELD_ID => $id], [], 1);
        return reset($result);
    }

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param null|int $limit
     * @param null|int $offset
     * @return array
     */
    public function findBy(array $criteria = [], array $orderBy = [], int $limit = null, int $offset = null): array
    {
        $result = UserTable::query()
            ->setSelect(['*', 'UF_*'])
            ->setFilter($criteria)
            ->setOrder($orderBy)
            ->setLimit($limit)
            ->setOffset($offset)
            ->exec();
        if (0 === $result->getSelectedRowsCount()) {
            return [];
        }

        /**
         * todo change group name to constant
         */
        return $this->arrayTransformer->fromArray(
            $result->fetchAll(),
            sprintf('array<%s>', trim(User::class)),
            DeserializationContext::create()->setGroups(['read'])
        );
    }

    public function update(User $user)
    {
        $validationResult = $this->validator->validate($user, null, ['update']);
        if ($validationResult->count() > 0) {
            /**
             * todo change to package exception
             */
            throw new \InvalidArgumentException();
        }
    }

    public function delete(int $id)
    {
        $validationResult = $this->validator->validate($id, [
            new NotBlank(),
            new GreaterThanOrEqual(['value' => 1]),
            new Type(['type' => 'integer']),
        ], ['delete']);
        if ($validationResult->count() > 0) {
            /**
             * todo change to package exception
             */
            throw new \InvalidArgumentException();
        }
        if (CUser::Delete($id)) {
            return true;
        }
        /**
         * todo throw exception
         */
    }
}
