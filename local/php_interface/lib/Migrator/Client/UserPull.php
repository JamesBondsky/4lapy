<?php

namespace FourPaws\Migrator\Client;

use FourPaws\Migrator\Provider\UserGroup as UserGroupProvider;
use FourPaws\Migrator\Provider\User as UserProvider;
use FourPaws\Migrator\Entity\UserGroup as UserGroupEntity;
use FourPaws\Migrator\Entity\User as UserEntity;

class UserPull extends ClientPullAbstract
{
    /**
     * @return \FourPaws\Migrator\Client\ClientInterface[] array
     */
    public function getBaseClientList() : array
    {
        return [
            new UserGroup(new UserGroupProvider(UserGroup::ENTITY_NAME, new UserGroupEntity(UserGroup::ENTITY_NAME)),
                          ['force' => $this->force]),
        ];
    }

    /**
     * @return \FourPaws\Migrator\Client\ClientInterface[] array
     */
    public function getClientList() : array
    {
        return [
            new User(new UserProvider(User::ENTITY_NAME, new UserEntity(User::ENTITY_NAME)), [
                                                                                               'limit' => $this->limit,
                                                                                               'force' => $this->force,
                                                                                           ]),
        ];
    }
}