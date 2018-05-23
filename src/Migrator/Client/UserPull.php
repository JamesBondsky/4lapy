<?php

namespace FourPaws\Migrator\Client;

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Migrator\Entity\User as UserEntity;
use FourPaws\Migrator\Provider\User as UserProvider;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Class UserPull
 *
 * @package FourPaws\Migrator\Client
 */
class UserPull extends ClientPullAbstract
{
    /**
     * @return ClientInterface[] array
     *
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function getBaseClientList(): array
    {
        return [
            /* new UserGroup(new UserGroupProvider(new UserGroupEntity(UserGroup::ENTITY_NAME)),
               ['force' => $this->force]),*/
        ];
    }

    /**
     * @return ClientInterface[] array
     *
     * @throws ApplicationCreateException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function getClientList(): array
    {
        return [
            new User(new UserProvider(new UserEntity(User::ENTITY_NAME)), [
                'limit' => $this->limit,
                'force' => $this->force,
            ]),
        ];
    }
}
