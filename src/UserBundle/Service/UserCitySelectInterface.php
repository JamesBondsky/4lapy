<?php

namespace FourPaws\UserBundle\Service;

interface UserCitySelectInterface
{
    /**
     * @param string $code
     * @param string $name
     * @param string $parentName
     *
     * @return bool
     * @throws \FourPaws\Location\Exception\CityNotFoundException
     */
    public function setSelectedCity(string $code = '', string $name = '', string $parentName = ''): bool;

    /**
     * @return array
     */
    public function getSelectedCity(): array;
}
