<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\SettingsRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Response;

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
     * @Parameter(
     *     name="token",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="identifier token from /start/ request"
     * )
     * @Response(
     *     response="200"
     * )
     *
     * @return ApiResponse
     *
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @Rest\View()
     * @throws \LogicException
     */
    public function getSettingsAction(): ApiResponse
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $response = (new ApiResponse())->setData([
            'settings' => [
                'interview_messaging_enabled' => $user->isSendInterviewMsg(),
                'bonus_messaging_enabled'     => $user->isSendBonusMsg(),
                'feedback_messaging_enabled'  => $user->isSendFeedbackMsg(),
                'push_order_status'           => $user->isSendOrderStatusMsg(),
                'push_news'                   => $user->isSendNewsMsg(),
                'push_account_change'         => $user->isSendBonusChangeMsg(),
                'sms_messaging_enabled'       => $user->isSendSmsMsg(),
                'email_messaging_enabled'     => $user->isSendEmailMsg(),
                'gps_messaging_enabled'       => $user->isGpsAllowed(),
            ],
        ]);

        return $response;
    }

    /**
     * @Rest\Post(path="/settings/", name="settings")
     * @Parameter(
     *     name="token",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="identifier token from /start/ request"
     * )
     * @Response(
     *     response="200"
     * )
     *
     * @param SettingsRequest $settingsRequest
     *
     * @return ApiResponse
     * @internal param Request $request
     *
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @Rest\View()
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws BitrixRuntimeException
     * @throws \LogicException
     */
    public function setSettingsAction(SettingsRequest $settingsRequest): ApiResponse
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        $settings = $settingsRequest->getSettings();

        $user->setSendInterviewMsg($settings->isSendInterviewMsg())
            ->setSendBonusMsg($settings->isSendBonusMsg())
            ->setSendFeedbackMsg($settings->isSendFeedbackMsg())
            ->setSendOrderStatusMsg($settings->isSendOrderStatusMsg())
            ->setSendNewsMsg($settings->isSendNewsMsg())
            ->setSendBonusChangeMsg($settings->isSendBonusChangeMsg())
            ->setSendSmsMsg($settings->isSendSmsMsg())
            ->setSendEmailMsg($settings->isSendEmailMsg())
            ->setGpsAllowed($settings->isGpsAllowed());

        $this->userService->getUserRepository()->update($user);

        $response = (new ApiResponse())->setData([
            'feedback_text' => 'Настройки приложения успешно сохранены',
        ]);

        return $response;
    }
}
