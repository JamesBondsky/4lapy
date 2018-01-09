<?php

namespace FourPaws\UserBundle\Service;

interface UserCitySelectInterface
{
    /**
     * @param string $code
     * @param string $name
     * @param string $parentName
     *
     * @return bool|array
     * @throws \FourPaws\Location\Exception\CityNotFoundException
     */
    public function setSelectedCity(string $code = '', string $name = '', string $parentName = '');

    /**
     * @return array
     */
    public function getSelectedCity(): array;
}
