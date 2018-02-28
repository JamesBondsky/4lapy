<?php

namespace FourPaws\MobileApiBundle\Repository;

use FourPaws\AppBundle\Enum\CrudGroups;
use FourPaws\MobileApiBundle\Entity\ApiUserSession;
use FourPaws\MobileApiBundle\Exception\BitrixException;
use FourPaws\MobileApiBundle\Exception\InvalidIdentifierException;
use FourPaws\MobileApiBundle\Exception\ValidationException;
use FourPaws\MobileApiBundle\Exception\WrongTransformerResultException;
use FourPaws\MobileApiBundle\Tables\ApiUserSessionTable;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiUserSessionRepository implements ApiUserSessionRepositoryInterface
{
    const FIELD_TOKEN = 'TOKEN';
    const FIELD_ID = 'ID';

    /**
     * @var ArrayTransformerInterface
     */
    private $transformer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ArrayTransformerInterface $transformer, ValidatorInterface $validator)
    {
        $this->transformer = $transformer;
        $this->validator = $validator;
    }

    /**
     * @param int $id
     *
     * @throws InvalidIdentifierException
     * @return null|ApiUserSession
     */
    public function find(int $id)
    {
        if ($id > 0) {
            $sessions = $this->findBy([static::FIELD_ID => $id], [], 1);
            return reset($sessions);
        }
        throw new InvalidIdentifierException('Wrong identifier passed: ' . $id);
    }

    /**
     * @param string $token
     *
     * @throws InvalidIdentifierException
     * @return null|ApiUserSession
     */
    public function findByToken(string $token)
    {
        if ($token) {
            $sessions = $this->findBy([static::FIELD_TOKEN => $token], [], 1);
            return reset($sessions);
        }
        throw new InvalidIdentifierException('Wrong identifier passed: ' . $token);
    }

    /**
     * @param array    $criteria
     * @param array    $orderBy
     * @param null|int $limit
     * @param null|int $offset
     *
     * @return ApiUserSession[]
     */
    public function findBy(array $criteria = [], array $orderBy = [], int $limit = null, int $offset = null): array
    {
        $query = ApiUserSessionTable::query()
            ->addSelect('*')
            ->addSelect('USER.ID', 'USER_ID');
        if ($criteria) {
            $query->setFilter($criteria);
        }
        if ($orderBy) {
            $query->setOrder($orderBy);
        }
        if ($limit) {
            $query->setLimit($limit);
        }
        if ($offset) {
            $query->setOffset($offset);
        }

        $dbResult = $query->exec();

        if ($dbResult->getSelectedRowsCount() === 0) {
            return [];
        }
        return $this->transformer->fromArray(
            $dbResult->fetchAll(),
            'array<' . ApiUserSession::class . '>',
            DeserializationContext::create()->setGroups([CrudGroups::READ])
        );
    }

    /**
     * @param ApiUserSession $session
     *
     * @throws ValidationException
     * @throws BitrixException
     * @throws WrongTransformerResultException
     * @return bool
     */
    public function create(ApiUserSession $session): bool
    {
        $validationResult = $this->validator->validate($session, null, [CrudGroups::CREATE]);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong session passed');
        }
        $data = $this
            ->transformer
            ->toArray(
                $session,
                SerializationContext::create()->setGroups([CrudGroups::CREATE])
            );
        if (!\is_array($data)) {
            throw new WrongTransformerResultException('Wrong transform result for session');
        }
        try {
            $result = ApiUserSessionTable::add($data);
        } catch (\Exception $exception) {
            throw new BitrixException($exception->getMessage(), $exception->getCode(), $exception);
        }
        if ($result->isSuccess()) {
            $session->setId($result->getId());
            $session->setToken($result->getData()['TOKEN']);
        }
        return $result->isSuccess();
    }

    /**
     * @param ApiUserSession $session
     *
     * @throws ValidationException
     * @throws BitrixException
     * @throws WrongTransformerResultException
     * @return bool
     */
    public function update(ApiUserSession $session): bool
    {
        $validationResult = $this->validator->validate($session, null, [CrudGroups::UPDATE]);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong session passed');
        }

        $data = $this
            ->transformer
            ->toArray(
                $session,
                SerializationContext::create()->setGroups([CrudGroups::UPDATE])
            );
        if (!\is_array($data)) {
            throw new WrongTransformerResultException('Wrong transform result for session');
        }
        try {
            $result = ApiUserSessionTable::update($session->getId(), $data);
        } catch (\Exception $exception) {
            throw new BitrixException($exception->getMessage(), $exception->getCode(), $exception);
        }
        return $result->isSuccess();
    }

    /**
     * @param int $id
     *
     * @throws InvalidIdentifierException
     * @throws BitrixException
     * @return bool
     */
    public function delete(int $id): bool
    {
        if ($id > 0) {
            try {
                $result = ApiUserSessionTable::delete($id);
            } catch (\Exception $exception) {
                throw new BitrixException($exception->getMessage(), $exception->getCode(), $exception);
            }
            return $result->isSuccess();
        }
        throw new InvalidIdentifierException('Wrong identifier passed: ' . $id);
    }

    /**
     * @param string $token
     *
     * @return bool
     * @throws \FourPaws\MobileApiBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\MobileApiBundle\Exception\BitrixException
     */
    public function deleteByToken(string $token): bool
    {
        if ($token) {
            try {
                $result = ApiUserSessionTable::query()
                    ->addSelect('ID')
                    ->addFilter('TOKEN', $token)
                    ->exec()
                    ->fetch();
                if ($result) {
                    return $this->delete((int)$result['ID']);
                }
                return true;

            } catch (\Exception $exception) {
                throw new BitrixException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }
        throw new InvalidIdentifierException('Wrong identifier passed: ' . $token);
    }
}
