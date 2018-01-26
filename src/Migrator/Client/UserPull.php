<?php

namespace FourPaws\Migrator\Client;

use FourPaws\Migrator\Entity\User as UserEntity;
use FourPaws\Migrator\Entity\UserGroup as UserGroupEntity;
use FourPaws\Migrator\Provider\User as UserProvider;
use FourPaws\Migrator\Provider\UserGroup as UserGroupProvider;

class UserPull extends ClientPullAbstract
{
    /**
     * @return \FourPaws\Migrator\Client\ClientInterface[] array
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function getBaseClientList() : array
    {
        return [
            new UserGroup(new UserGroupProvider(new UserGroupEntity(UserGroup::ENTITY_NAME)),
                          ['force' => $this->force]),
        ];
    }
    
    /**
     * @return \FourPaws\Migrator\Client\ClientInterface[] array
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function getClientList() : array
    {
        return [
            new User(new UserProvider(new UserEntity(User::ENTITY_NAME)), [
                'limit' => $this->limit,
                'force' => $this->force,
            ]),
        ];
    }
}
