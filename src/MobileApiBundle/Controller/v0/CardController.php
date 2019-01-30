<?php

/**
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\CardActivatedRequest;
use FourPaws\MobileApiBundle\Dto\Request\ChangeCardConfirmPersonalRequest;
use FourPaws\MobileApiBundle\Dto\Request\ChangeCardConfirmPinRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FourPaws\MobileApiBundle\Services\Api\CardService as ApiCardService;
use FourPaws\MobileApiBundle\Dto\Object\ClientCard;
use FourPaws\MobileApiBundle\Dto\Object\User;
use FourPaws\MobileApiBundle\Dto\Request\ChangeCardValidateRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserAddCartRequest;
use FourPaws\MobileApiBundle\Dto\Response\FeedbackResponse;
use FourPaws\MobileApiBundle\Services\Api\UserService as ApiUserService;
use FourPaws\MobileApiBundle\Services\Api\CaptchaService as ApiCaptchaService;


class CardController extends FOSRestController
{
    /**
     * @var ApiCardService
     */
    private $apiCardService;

    /**
     * @var ApiUserService
     */
    private $apiUserService;

    /**
     * @var ApiCaptchaService
     */
    private $apiCaptchaService;

    public function __construct(
        ApiCardService $apiCardService,
        ApiUserService $apiUserService,
        ApiCaptchaService $apiCaptchaService
    )
    {
        $this->apiCardService = $apiCardService;
        $this->apiUserService = $apiUserService;
        $this->apiCaptchaService = $apiCaptchaService;
    }

    /**
     * @Rest\Get("/card_activated/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     * @param CardActivatedRequest $cardActivatedRequest
     * @return Response
     */
    public function isCardActivatedAction(CardActivatedRequest $cardActivatedRequest): Response
    {
        return $this->apiCardService->isActive($cardActivatedRequest->getCardNumber());
    }

    /**
     * @Rest\Post("/change_card_validate/")
     * @Rest\View()
     * @param ChangeCardValidateRequest $changeCardValidateRequest
     * @return Response
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\External\Manzana\Exception\CardNotFoundException
     */
    public function getCardDataFromManzanaAction(ChangeCardValidateRequest $changeCardValidateRequest)
    {
        return (new Response())
            ->setData([
                'profile' => $this->apiCardService->getCardDataFromManzana($changeCardValidateRequest->getNewCardNumber())
            ]);
    }

    /**
     * @Rest\Post("/change_card_confirm_personal/")
     * @Rest\View()
     * @param ChangeCardConfirmPersonalRequest $cardConfirmPersonalRequest
     * @return Response\CaptchaSendValidationResponse
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\UserBundle\Exception\NotFoundException
     *
     * Задача: отправить на мыло из запроса код проверки
     */
    public function postChangeCardConfirmPersonalAction(ChangeCardConfirmPersonalRequest $cardConfirmPersonalRequest)
    {
        return $this->apiCaptchaService->sendValidation($cardConfirmPersonalRequest->getProfile()->getEmail(), 'card_activation');
    }

    /**
     * @Rest\Post("/change_card_confirm_pin/")
     * @Rest\View()
     * @param ChangeCardConfirmPinRequest $changeCardConfirmPinRequest
     * @return FeedbackResponse
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     *
     * Задача: проверить капчу, и если все гут - апдейтить юзера в БД
     */
    public function postChangeCardConfirmPinAction(ChangeCardConfirmPinRequest $changeCardConfirmPinRequest)
    {
        $this->apiCardService->changeCardConfirmPin(
            $changeCardConfirmPinRequest->getProfile(),
            $changeCardConfirmPinRequest->getCaptchaId(),
            $changeCardConfirmPinRequest->getCaptchaValue()
        );
        return (new FeedbackResponse('Карта успешно привязана'));
    }

    /**
     * @Rest\Get("/user_addcard/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     * @param UserAddCartRequest $userAddCartRequest
     * @return FeedbackResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     */
    public function getUserAddCardAction(UserAddCartRequest $userAddCartRequest)
    {
        $user = (new User())
            ->setCard((new ClientCard())
                ->setNumber($userAddCartRequest->getNewCardNumber())
            )
            ->setFirstName($userAddCartRequest->getFirstName())
            ->setLastName($userAddCartRequest->getLastName())
            ->setBirthDate($userAddCartRequest->getBirthDate()->format('d.m.Y'))
            ->setPhone($userAddCartRequest->getPhone());

        if ($middleName = $userAddCartRequest->getSecondName()) {
            $user->setMidName($middleName);
        }

        if ($email = $userAddCartRequest->getEmail()) {
            $user->setEmail($email);
        }
        $this->apiUserService->update($user);
        return (new FeedbackResponse('Карта успешно привязана'));
    }
}
