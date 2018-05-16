<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\UserBundle\Service;

use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\NotFoundException;

/**
 * Interface UserSearchInterface
 * 
 * @package FourPaws\UserBundle\Service
 */
interface UserSearchInterface
{
    /**
     * @param int $id
     *
     * @return User
     * @throws NotFoundException
     */
    public function findOne(int $id): User;

    /**
     * @param string $phone
     * @param string $email
     *
     * @throws NotFoundException
     * @return User
     */
    public function findOneByPhoneOrEmail(string $phone, string $email): User;

    /**
     * @param string $email
     *
     * @throws NotFoundException
     * @return User
     */
    public function findOneByEmail(string $email): User;

    /**
     * @param string $phone
     *
     * @throws NotFoundException
     * @return User
     */
    public function findOneByPhone(string $phone): User;
}
