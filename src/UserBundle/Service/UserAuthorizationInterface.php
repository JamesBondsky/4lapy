<?php

namespace FourPaws\UserBundle\Service;

use FourPaws\UserBundle\Exception\InvalidCredentialException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;

interface UserAuthorizationInterface
{
    /**
     * @param string $login
     * @param string $password
     *
     * @throws UsernameNotFoundException
     * @throws TooManyUserFoundException
     * @throws InvalidCredentialException
     * @return bool
     */
    public function login(string $login, string $password): bool;

    /**
     * @return bool
     */
    public function logout(): bool;

    /**
     * @return bool
     */
    public function isAuthorized(): bool;

    /**
     * @param int $id
     *
     * @return bool
     */
    public function authorize(int $id): bool;
}
