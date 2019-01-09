<?php

namespace FourPaws\MobileApiBundle\Repository;

use FourPaws\AppBundle\Enum\CrudGroups;
use FourPaws\MobileApiBundle\Entity\ApiPushEvent;
use FourPaws\MobileApiBundle\Exception\BitrixException;
use FourPaws\MobileApiBundle\Exception\InvalidIdentifierException;
use FourPaws\MobileApiBundle\Exception\ValidationException;
use FourPaws\MobileApiBundle\Exception\WrongTransformerResultException;
use FourPaws\MobileApiBundle\Tables\ApiPushEventTable;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiPushEventRepository implements ApiPushEventRepositoryInterface
{
    const FIELD_ID = 'ID';
    const PLATFORM_ANDROID = 'a';
    const PLATFORM_IOS = 'i';

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
     * @return null|ApiPushEvent
     */
    public function find(int $id)
    {
        if ($id > 0) {
            $pushEvents = $this->findBy([static::FIELD_ID => $id], [], 1);
            return reset($pushEvents);
        }
        throw new InvalidIdentifierException('Wrong identifier passed: ' . $id);
    }

    public function findForAndroid()
    {
        return $this->findBy([
            '=PLATFORM' => static::PLATFORM_ANDROID,
            '<=DATE_TIME_EXEC' => new \Bitrix\Main\Type\DateTime(),
            '=SUCCESS_EXEC' => ApiPushEvent::EXEC_PENDING_CODE
        ]);
    }

    public function findForIos()
    {
        return $this->findBy([
            '=PLATFORM' => static::PLATFORM_IOS,
            '<=DATE_TIME_EXEC' => new \Bitrix\Main\Type\DateTime(),
            '=SUCCESS_EXEC' => ApiPushEvent::EXEC_PENDING_CODE
        ]);
    }

    /**
     * @param array    $criteria
     * @param array    $orderBy
     * @param null|int $limit
     * @param null|int $offset
     *
     * @return ApiPushEvent[]
     */
    public function findBy(array $criteria = [], array $orderBy = [], int $limit = null, int $offset = null): array
    {
        $query = ApiPushEventTable::query()
            ->addSelect('*')
            ->addSelect('MESSAGE.UF_MESSAGE', 'MESSAGE_TEXT')
            ->addSelect('MESSAGE.UF_TYPE', 'MESSAGE_TYPE');
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
            'array<' . ApiPushEvent::class . '>',
            DeserializationContext::create()->setGroups([CrudGroups::READ])
        );
    }

    /**
     * @param ApiPushEvent $pushEvent
     *
     * @throws ValidationException
     * @throws BitrixException
     * @throws WrongTransformerResultException
     * @return bool
     */
    public function create(ApiPushEvent $pushEvent): bool
    {
        $validationResult = $this->validator->validate($pushEvent, null, [CrudGroups::CREATE]);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong push event passed');
        }
        $data = $this
            ->transformer
            ->toArray(
                $pushEvent,
                SerializationContext::create()->setGroups([CrudGroups::CREATE])
            );
        if (!\is_array($data)) {
            throw new WrongTransformerResultException('Wrong transform result for push event');
        }
        try {
            $result = ApiPushEventTable::add($data);
        } catch (\Exception $exception) {
            throw new BitrixException($exception->getMessage(), $exception->getCode(), $exception);
        }
        return $result->isSuccess();
    }

    /**
     * @param ApiPushEvent $pushEvent
     *
     * @throws ValidationException
     * @throws BitrixException
     * @throws WrongTransformerResultException
     * @return bool
     */
    public function update(ApiPushEvent $pushEvent): bool
    {
        $validationResult = $this->validator->validate($pushEvent, null, [CrudGroups::UPDATE]);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong push event passed');
        }

        $data = $this
            ->transformer
            ->toArray(
                $pushEvent,
                SerializationContext::create()->setGroups([CrudGroups::UPDATE])
            );
        if (!\is_array($data)) {
            throw new WrongTransformerResultException('Wrong transform result for push event');
        }
        try {
            $result = ApiPushEventTable::update($pushEvent->getId(), $data);
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
                $result = ApiPushEventTable::delete($id);
            } catch (\Exception $exception) {
                throw new BitrixException($exception->getMessage(), $exception->getCode(), $exception);
            }
            return $result->isSuccess();
        }
        throw new InvalidIdentifierException('Wrong identifier passed: ' . $id);
    }
}
