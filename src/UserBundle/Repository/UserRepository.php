<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Repository;

use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserGroupTable;
use Bitrix\Main\UserTable;
use CUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\AppBundle\Service\LazyCallbackValueLoader;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\UserBundle\Entity\Group;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Exception\ValidationException;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use ProxyManager\Proxy\VirtualProxyInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserRepository
{
    public const FIELD_ID = 'ID';

    /** @var ArrayTransformerInterface $builder */
    protected $arrayTransformer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var CUser
     */
    private $cuser;

    /**
     * @var \CAllMain|\CMain
     */
    private $cmain;

    /**
     * @var LazyCallbackValueLoader
     */
    private $lazyCallbackValueLoader;

    /**
     * UserRepository constructor.
     *
     * @param ValidatorInterface        $validator
     *
     * @param LazyCallbackValueLoader   $lazyCallbackValueLoader
     *
     * @param ArrayTransformerInterface $arrayTransformer
     */
    public function __construct(
        ValidatorInterface $validator,
        LazyCallbackValueLoader $lazyCallbackValueLoader,
        ArrayTransformerInterface $arrayTransformer
    ) {
        $this->arrayTransformer = $arrayTransformer;

        $this->cuser = new \CUser();
        $this->validator = $validator;
        global $APPLICATION;
        $this->cmain = $APPLICATION;
        $this->lazyCallbackValueLoader = $lazyCallbackValueLoader;
    }

    /**
     * @param User $user
     *
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @return bool
     */
    public function create(User $user): bool
    {
        $validationResult = $this->validator->validate($user, null, ['create']);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong entity passed to create');
        }

        $result = $this->cuser->Add(
            $this->arrayTransformer->toArray($user, SerializationContext::create()->setGroups(['create']))
        );
        $userId = (int)$result;
        if ($userId > 0) {
            $user->setId($userId);

            return true;
        }

        throw new BitrixRuntimeException($this->cuser->LAST_ERROR);
    }

    /**
     * @param int $id
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @return null|User
     */
    public function find(int $id): ?User
    {
        $this->checkIdentifier($id);
        $result = $this->findBy([static::FIELD_ID => $id], [], 1);

        return reset($result);
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param array    $criteria
     * @param array    $orderBy
     * @param null|int $limit
     * @param null|int $offset
     *
     * @return User[]
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
        $users = $this->arrayTransformer->fromArray(
            $result->fetchAll(),
            sprintf('array<%s>', User::class),
            DeserializationContext::create()->setGroups(['read'])
        );

        return array_map(
            function (User $user) {
                /**
                 * @var Collection|VirtualProxyInterface $groups
                 */
                $groups = $this->lazyCallbackValueLoader->load(
                    ArrayCollection::class,
                    function () use ($user) {
                        return $this->getUserGroups($user->getId());
                    }
                );
                $user->setGroups($groups);

                return $user;
            },
            $users ?: []
        );
    }

    /**
     * @param string $rawLogin
     *
     * @param bool   $onlyActive
     *
     * @throws UsernameNotFoundException
     * @throws TooManyUserFoundException
     * @throws WrongPhoneNumberException
     * @return int
     */
    public function findIdentifierByRawLogin(string $rawLogin, bool $onlyActive = true): int
    {
        return (int)$this->findIdAndLoginByRawLogin($rawLogin, $onlyActive)['ID'];
    }

    /**
     * @param string $rawLogin
     *
     * @param bool   $onlyActive
     *
     * @throws UsernameNotFoundException
     * @throws TooManyUserFoundException
     * @throws WrongPhoneNumberException
     * @return string
     */
    public function findLoginByRawLogin(string $rawLogin, bool $onlyActive = true): string
    {
        return (string)$this->findIdAndLoginByRawLogin($rawLogin, $onlyActive)['LOGIN'];
    }

    /**
     * @param string $rawLogin
     * @param bool   $onlyActive
     *
     * @throws TooManyUserFoundException
     * @return bool
     */
    public function isExist(string $rawLogin, bool $onlyActive = true): bool
    {
        try {
            $this->findIdAndLoginByRawLogin($rawLogin, $onlyActive);

            return true;
        } catch (UsernameNotFoundException $exception) {
        } catch (WrongPhoneNumberException $e) {
        }

        return false;
    }

    /**
     * @param User $user
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @return bool
     */
    public function update(User $user): bool
    {
        $validationResult = $this->validator->validate($user, null, ['update']);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong entity passed to update');
        }

        return $this->updateData(
            $user->getId(),
            $this->arrayTransformer->toArray($user, SerializationContext::create()->setGroups(['update']))
        );
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @throws InvalidIdentifierException
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @return bool
     */
    public function updateData(int $id, array $data): bool
    {
        $this->checkIdentifier($id);
        if ($this->cuser->Update(
            $id,
            $data
        )) {
            TaggedCacheHelper::clearManagedCache([
                'personal:profile' . $id,
            ]);

            return true;
        }
        throw new BitrixRuntimeException($this->cuser->LAST_ERROR);
    }

    /**
     * @param int    $id
     * @param string $password
     *
     * @throws InvalidIdentifierException
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @return bool
     */
    public function updatePassword(int $id, string $password): bool
    {
        return $this->updateData($id, ['PASSWORD' => $password]);
    }

    /**
     * @param int    $id
     * @param string $phone
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws BitrixRuntimeException
     * @return bool
     */
    public function updatePhone(int $id, string $phone): bool
    {
        return $this->updateData($id, ['PERSONAL_PHONE' => $phone]);
    }

    /**
     * @param int    $id
     * @param string $email
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws BitrixRuntimeException
     * @return bool
     */
    public function updateEmail(int $id, string $email): bool
    {
        return $this->updateData($id, ['EMAIL' => $email]);
    }

    /**
     * @param int    $id
     * @param string $discountCardNumber
     *
     * @return bool
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws BitrixRuntimeException
     */
    public function updateDiscountCard(int $id, string $discountCardNumber): bool
    {
        return $this->updateData($id, ['UF_DISCOUNT_CARD' => $discountCardNumber]);
    }

    /**
     * @param int $id
     *
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws BitrixRuntimeException
     * @return bool
     */
    public function delete(int $id): bool
    {
        $this->checkIdentifier($id);
        if (CUser::Delete($id)) {
            return true;
        }

        $bitrixException = $this->cmain->GetException();
        throw new BitrixRuntimeException($bitrixException->GetString(), $bitrixException->GetID() ?: null);
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getUserGroupsIds(int $id): array
    {
        return $this->getUserGroups($id)->map(function (Group $group) {
            return $group->getId();
        })->toArray();
    }

    public function havePhoneAndEmailByUsers(array $params): array
    {
        $return = [
            'phone' => false,
            'email' => false,
            'login' => false
        ];

        if (empty($params)) {
            return $return;
        }

        $filter = [
            [
                'LOGIC' => 'OR',
            ],
        ];
        if (!empty($params['EMAIL'])) {
            $filter[0]['EMAIL'] = $params['EMAIL'];
        }
        if (!empty($params['PERSONAL_PHONE'])) {
            $filter[0]['PERSONAL_PHONE'] = $params['PERSONAL_PHONE'];
        }
        if (\count($filter[0]) === 2) {
            $val = end($filter[0]);
            $filter[key($filter[0])] = $val;
            unset($filter[0]);
        }
        if (!empty($params['ID'])) {
            $filter['!ID'] = $params['ID'];
        }
        $users = $this->findBy(
            $filter,
            [],
            1
        );
        if (\is_array($users) && !empty($users)) {
            /** @var User $user */
            foreach ($users as $user) {
                if (!$return['phone'] && $user->getPersonalPhone() === $params['PERSONAL_PHONE']) {
                    $return['phone'] = true;
                }
                if (!$return['email'] && $user->getEmail() === $params['EMAIL']) {
                    $return['email'] = true;
                }
                if (!$return['login'] && ($user->getLogin() === $params['EMAIL'] || $user->getLogin() === $params['PERSONAL_PHONE'])) {
                    $return['login'] = true;
                }
            }
        }

        return $return;
    }

    /**
     * @param array  $data
     * @param string $group
     *
     * @return array
     */
    public function prepareData(array $data, string $group = 'update'): array
    {
        $formattedData = $this->arrayTransformer->toArray(
            $this->arrayTransformer->fromArray($data, User::class, DeserializationContext::create()->setGroups([$group])),
            SerializationContext::create()->setGroups([$group])
        );
        foreach ($data as $key => $val) {
            if (!array_key_exists($key, $formattedData)) {
                unset($data[$key]);
            }
        }
        if (isset($data['ID'])) {
            unset($data['ID']);
        }

        return $data;
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /** @noinspection PhpDocMissingThrowsInspection
     * @param int $id
     *
     * @return Collection|Group[]
     */
    protected function getUserGroups(int $id): Collection
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $res = UserGroupTable::query()
            ->setFilter([
                'USER_ID'       => $id,
                '!GROUP.ACTIVE' => null,
                'LOGIC'         => 'AND',
                [
                    'LOGIC'            => 'OR',
                    '>=DATE_ACTIVE_TO' => new DateTime(),
                    'DATE_ACTIVE_TO'   => null,
                ],
                [
                    'LOGIC'              => 'OR',
                    '<=DATE_ACTIVE_FROM' => new DateTime(),
                    'DATE_ACTIVE_FROM'   => null,
                ],
            ])
            ->addSelect('GROUP_ID')
            ->addSelect('GROUP.NAME', 'GROUP_NAME')
            ->addSelect('GROUP.STRING_ID', 'GROUP_CODE')
            ->addSelect('GROUP.NAME', 'GROUP_NAME')
            ->addSelect('GROUP.ACTIVE', 'GROUP_ACTIVE')
            ->exec();

        $data = array_filter($res->fetchAll(), function ($group) {
            return $group && $group['GROUP_ACTIVE'];
        });

        $groups = $this->arrayTransformer->fromArray($data, sprintf('array<%s>', Group::class)) ?? [];
        return new ArrayCollection($groups);
    }

    /**
     * @param string $rawLogin
     *
     * @param bool   $onlyActive
     *
     * @throws UsernameNotFoundException
     * @throws TooManyUserFoundException
     * @throws WrongPhoneNumberException
     * @return array
     */
    protected function findIdAndLoginByRawLogin(string $rawLogin, bool $onlyActive = true): array
    {
        $filter = [
            '=LOGIN' => $rawLogin,
        ];

        if (filter_var($rawLogin, FILTER_VALIDATE_EMAIL)) {
            $filter = [
                [
                    [
                        'LOGIC' => 'OR',
                        $filter,
                        [
                            '=EMAIL' => $rawLogin,
                        ],
                    ],
                ],
            ];
        }

        if (PhoneHelper::isPhone($rawLogin)) {
            $filter = [
                [
                    [
                        'LOGIC' => 'OR',
                        [
                            '=PERSONAL_PHONE' => $rawLogin,
                        ],
                        [
                            '=PERSONAL_PHONE' => PhoneHelper::normalizePhone($rawLogin),
                        ],
                    ],
                ],
            ];
        }

        $query = UserTable::query()
            ->addSelect('ID')
            ->addSelect('LOGIN')
            ->setFilter($filter);

        if ($onlyActive) {
            $query->addFilter('ACTIVE', 'Y');
        }
        $result = $query->exec();

        $data = $result->fetchRaw() ?: [];
        $data = array_filter($data);
        $isValidData = isset($data['ID'], $data['LOGIN']);

        if ($isValidData && 1 === $result->getSelectedRowsCount()) {
            return $data;
        }

        if (!$isValidData || 0 === $result->getSelectedRowsCount()) {
            throw new UsernameNotFoundException(sprintf('No user with such raw login %s', $rawLogin));
        }

        throw new TooManyUserFoundException('Found more than one user with same raw login');
    }

    /**
     * @param int $id
     *
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     */
    protected function checkIdentifier(int $id): void
    {
        try {
            $result = $this->validator->validate($id, [
                new NotBlank(),
                new GreaterThanOrEqual(['value' => 1]),
                new Type(['type' => 'integer']),
            ], ['delete']);
        } catch (ValidatorException $exception) {
            throw new ConstraintDefinitionException('Wrong constraint configuration');
        }
        if ($result->count()) {
            throw new InvalidIdentifierException(sprintf('Wrong identifier %s passed', $id));
        }
    }
}
