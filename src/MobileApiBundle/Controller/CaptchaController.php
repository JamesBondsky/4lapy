<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\CaptchaCreateRequest;
use FourPaws\MobileApiBundle\Dto\Request\CaptchaVerifyRequest;
use FourPaws\MobileApiBundle\Services\Api\CaptchaService as ApiCaptchaService;
use FourPaws\MobileApiBundle\Dto\Response\CaptchaVerifyResponse;
use FourPaws\MobileApiBundle\Dto\Response\CaptchaSendValidationResponse;

class CaptchaController extends FOSRestController
{
    /**
     * @var ApiCaptchaService
     */
    private $apiCaptchaService;

    public function __construct(ApiCaptchaService $apiCaptchaService)
    {
        $this->apiCaptchaService = $apiCaptchaService;
    }

    /**
     * @Rest\Post(path="/captcha/")
     * @Rest\View()
     *
     * @param CaptchaCreateRequest $captchaCreateRequest
     * @return CaptchaSendValidationResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\External\Exception\ExpertsenderServiceException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \LinguaLeo\ExpertSender\ExpertSenderException
     */
    public function sendCaptchaValidationAction(CaptchaCreateRequest $captchaCreateRequest)
    {
        return $this->apiCaptchaService->sendValidation(
            $captchaCreateRequest->getLogin(),
            $captchaCreateRequest->getSender()
        );
    }

    /**
     * Вызывается когда пользователь меняет телефон или email
     *
     * @Rest\Post(path="/verify/")
     * @Rest\View()
     *
     * @param CaptchaVerifyRequest $captchaVerifyRequest
     * @return CaptchaVerifyResponse
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException
     */
    public function verifyCaptchaAction(CaptchaVerifyRequest $captchaVerifyRequest)
    {
        return $this->apiCaptchaService->verify(
            $captchaVerifyRequest->getLogin(),
            $captchaVerifyRequest->getCaptchaId(),
            $captchaVerifyRequest->getCaptchaValue()
        );
    }
}
