<?php

namespace FourPaws\MobileApiBundle\Services\Api;

use FourPaws\MobileApiBundle\Dto\Request\QuestRegisterRequest;

class QuestService
{
    public function __construct()
    {

    }

    /**
     * @param QuestRegisterRequest $questRegisterRequest
     * @return array
     */
    public function registerUser(QuestRegisterRequest $questRegisterRequest): array
    {
        return [];
    }
}
