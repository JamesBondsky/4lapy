<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\CaptchaCreateRequest;
use FourPaws\MobileApiBundle\Dto\Request\CaptchaVerifyRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Services\Api\CaptchaService as ApiCaptchaService;

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
     * @return ApiResponse
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\UserBundle\Exception\NotFoundException
     */
    public function createAction(CaptchaCreateRequest $captchaCreateRequest)
    {
        $data = $this->apiCaptchaService->sendValidation(
            $captchaCreateRequest->getLogin(),
            $captchaCreateRequest->getSender()
        );
        return (new ApiResponse())->setData($data);
    }

    /**
     * @Rest\Post(path="/verify/")
     * @Rest\View()
     *
     * @param CaptchaVerifyRequest $captchaVerifyRequest
     * @return ApiResponse
     */
    public function verifyAction(CaptchaVerifyRequest $captchaVerifyRequest)
    {
        $data = $this->apiCaptchaService->verify(
            $captchaVerifyRequest->getEntity(),
            $captchaVerifyRequest->getCaptchaId(),
            $captchaVerifyRequest->getCaptchaValue()
        );
        return (new ApiResponse())->setData($data);
    }
}
