<?php

/*
 * @copyright Copyright (c) NotAgency
 */
namespace FourPaws\AppBundle\Traits;

use FourPaws\App\Application;
use FourPaws\AppBundle\Service\UserFieldEnumService;

trait UserFieldEnumTrait
{
    /** @var UserFieldEnumService $userFieldEnumService */
    private $userFieldEnumService;

    /**
     * @return UserFieldEnumService
     */
    protected function getUserFieldEnumService() : UserFieldEnumService
    {
        if (!$this->userFieldEnumService) {
            $this->userFieldEnumService = Application::getInstance()->getContainer()->get('userfield_enum.service');
        }

        return $this->userFieldEnumService;
    }
}