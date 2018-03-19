<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Object\Settings;
use FourPaws\MobileApiBundle\Dto\Request\SettingsRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class SettingsController extends FOSRestController
{
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @Rest\Get(path="/settings/", name="settings")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     * @throws \LogicException
     * @return ApiResponse
     *
     */
    public function getSettingsAction(): ApiResponse
    {
        return (new ApiResponse())->setData([
            'settings' => Settings::createFromUser($this->getUser()),
        ]);
    }

    /**
     * @Rest\Post(path="/settings/", name="settings")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param SettingsRequest $settingsRequest
     * @throws \LogicException
     * @throws \Bitrix\Main\SystemException
     * @return ApiResponse
     */
    public function setSettingsAction(SettingsRequest $settingsRequest): ApiResponse
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $settings = $settingsRequest->getSettings();
        $settings->configureUser($user);

        $this->userService->getUserRepository()->update($user);

        return (new ApiResponse())->setData([
            'feedback_text' => 'Настройки приложения успешно сохранены',
        ]);
    }
}
