<?php

/**
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Request\CardActivatedRequest;
use FourPaws\MobileApiBundle\Dto\Request\ChangeCardConfirmPersonalRequest;
use FourPaws\MobileApiBundle\Dto\Request\ChangeCardConfirmPinRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Exception\CardAlreadyUsedException;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FourPaws\MobileApiBundle\Services\Api\CardService as ApiCardService;
use FourPaws\MobileApiBundle\Dto\Object\ClientCard;
use FourPaws\MobileApiBundle\Dto\Object\User;
use FourPaws\MobileApiBundle\Dto\Request\ChangeCardValidateRequest;
use FourPaws\MobileApiBundle\Dto\Request\UserAddCartRequest;
use FourPaws\MobileApiBundle\Dto\Response\FeedbackResponse;
use FourPaws\MobileApiBundle\Services\Api\UserService as ApiUserService;
use FourPaws\MobileApiBundle\Dto\Response\CardActivatedResponse;


class CardController extends BaseController
{
    /**
     * @var ApiCardService
     */
    private $apiCardService;

    /**
     * @var ApiUserService
     */
    private $apiUserService;

    public function __construct(
        ApiCardService $apiCardService,
        ApiUserService $apiUserService
    )
    {
        $this->apiCardService = $apiCardService;
        $this->apiUserService = $apiUserService;
    }

    /**
     * @Rest\Get("/card_activated/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     * @param CardActivatedRequest $cardActivatedRequest
     * @return CardActivatedResponse
     */
    public function isCardActivatedAction(CardActivatedRequest $cardActivatedRequest): CardActivatedResponse
    {
        if ($activated = $this->apiCardService->isActive($cardActivatedRequest->getCardNumber())) {
            throw new CardAlreadyUsedException();
        }

        return new CardActivatedResponse(
            $activated,
            $activated ? 'Карта уже привязана к другому аккаунту. Пожалуйста, используйте другую карту' : ''
        );
    }

    /**
     * Проверяет на валидность новый номер карты
     *
     * @Rest\Post("/change_card_validate/")
     * @Rest\View()
     * @param ChangeCardValidateRequest $changeCardValidateRequest
     * @return Response
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\External\Manzana\Exception\CardNotFoundException
     */
    public function postChangeCardConfirmAction(ChangeCardValidateRequest $changeCardValidateRequest)
    {

        $newCardNumber = $changeCardValidateRequest->getNewCardNumber();
        if ($apiResponse = $this->apiCardService->isActive($newCardNumber)) {
            throw new CardAlreadyUsedException();
        }
        return (new Response())
            ->setData([
                'profile' => $this->apiCardService->getCardDataFromManzana($newCardNumber)
            ]);
    }

    /**
     * Отправляет подтверждающее письмо о смене карты на email
     *
     * @Rest\Post("/change_card_confirm_personal/")
     * @Rest\View()
     * @param ChangeCardConfirmPersonalRequest $cardConfirmPersonalRequest
     * @return Response\CaptchaSendValidationResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ExpertsenderServiceException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \LinguaLeo\ExpertSender\ExpertSenderException
     */
    public function postChangeCardConfirmPersonalAction(ChangeCardConfirmPersonalRequest $cardConfirmPersonalRequest)
    {
        return $this->apiCardService->sendConfirmationToEmail(
            $cardConfirmPersonalRequest->getProfile()->getEmail()
        );
    }

    /**
     * Проверяет код подтверждения и меняет карту на новую
     *
     * @Rest\Post("/change_card_confirm_pin/")
     * @Rest\View()
     * @param ChangeCardConfirmPinRequest $changeCardConfirmPinRequest
     * @return FeedbackResponse
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException
     * @throws \FourPaws\External\Exception\ManzanaServiceContactSearchNullException
     * @throws \FourPaws\External\Exception\ManzanaServiceException Задача: проверить капчу, и если все гут - апдейтить юзера в БД
     * @throws \FourPaws\PersonalBundle\Exception\CardNotValidException
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException
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
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
     */
    public function getUserAddCardAction(UserAddCartRequest $userAddCartRequest)
    {
        $user = (new User())
            ->setCard((new ClientCard())
                ->setNumber($userAddCartRequest->getNewCardNumber())
            )
            ->setBirthDate($userAddCartRequest->getBirthDate()->format('d.m.Y'))
            ->setPhone($userAddCartRequest->getPhone());

        if ($firstName = $userAddCartRequest->getFirstName()) {
            $user->setFirstName($firstName);
        }

        if ($lastName = $userAddCartRequest->getLastName()) {
            $user->setLastName($lastName);
        }

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
