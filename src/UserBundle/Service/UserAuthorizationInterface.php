<?php

namespace FourPaws\UserBundle\Service;

use FourPaws\UserBundle\Exception\InvalidCredentialException;

interface UserAuthorizationInterface
{
    /**
     * @param string $login
     * @param string $password
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
     * @return bool
     */
    public function authorize(int $id): bool;
}
