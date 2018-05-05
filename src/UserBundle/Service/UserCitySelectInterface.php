<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserBundle\Service;

use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;

interface UserCitySelectInterface
{
    /**
     * @param string $code
     * @param string $name
     * @param string|array|null $parentName
     *
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws CityNotFoundException
     * @throws NotAuthorizedException
     * @throws BitrixRuntimeException
     * @return array|bool
     */
    public function setSelectedCity(string $code = '', string $name = '', string $parentName = null);

    /**
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws NotAuthorizedException
     * @return array
     */
    public function getSelectedCity(): array;
}
