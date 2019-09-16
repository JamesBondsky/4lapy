<?php

namespace FourPaws\ManzanaApiBundle\Controller;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Application;
use FourPaws\ManzanaApiBundle\Dto\Request\CouponsAddRequest;
use FourPaws\ManzanaApiBundle\Dto\Request\CouponsIssueRequest;
use FourPaws\ManzanaApiBundle\Dto\Request\CouponsSetUsedRequest;
use FourPaws\ManzanaApiBundle\Exception\InvalidArgumentException;
use FourPaws\ManzanaApiBundle\Service\ManzanaApiService;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * Class ManzanaController
 *
 * @package FourPaws\ManzanaApiBundle\Controller
 *
 * @Route("api_manzana/")
 */
class ManzanaController extends Controller
{
    use LazyLoggerAwareTrait;

    /**
     * @var ManzanaApiService $manzanaApiService
     */
    protected $manzanaApiService;
    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    /**
     * ManzanaController constructor.
     */
    public function __construct()
    {
        $this->manzanaApiService = Application::getInstance()->getContainer()->get('manzana_api.service');
        $this->arrayTransformer = Application::getInstance()->getContainer()->get(SerializerInterface::class);
    }

    /**
     * @Route("coupons_issue/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function couponsIssue(Request $request): JsonResponse
    {
        try {
            parse_str($request->getContent(), $requestObject);

            /** @var CouponsIssueRequest $couponsIssueRequest */
            $couponsIssueRequest = $this->arrayTransformer->fromArray($requestObject, CouponsIssueRequest::class);

            $result = $this->manzanaApiService->addOrUpdateCouponIssue($couponsIssueRequest->getCouponsIssues());

            return new JsonResponse(
                ['messages' => $this->arrayTransformer->toArray($result->getMessages())],
                ManzanaApiService::RESPONSE_STATUSES['success']['code']
            );
        } catch (Throwable $e) {
            $this->log()->critical(__METHOD__ . ' exception: ' . $e->getMessage(), [$e->getTrace()]);

            return new JsonResponse(
                ['error' => 'Что-то пошло не так'],
                ManzanaApiService::RESPONSE_STATUSES['internal_error']['code']
            );
        }
    }

    /**
     * @Route("coupons/add/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function couponsAdd(Request $request): JsonResponse
    {
        try {
            parse_str($request->getContent(), $requestObject);

            /** @var CouponsAddRequest $couponsAddRequest */
            $couponsAddRequest = $this->arrayTransformer->fromArray($requestObject, CouponsAddRequest::class);

            $result = $this->manzanaApiService->addCoupons($couponsAddRequest->getCoupons());

            return new JsonResponse(
                ['messages' => $this->arrayTransformer->toArray($result->getMessages())],
                ManzanaApiService::RESPONSE_STATUSES['success']['code']
            );
        } catch (Throwable $e) {
            $this->log()->critical(__METHOD__ . ' exception: ' . $e->getMessage(), [$e->getTrace()]);

            return new JsonResponse(
                ['error' => 'Что-то пошло не так'],
                ManzanaApiService::RESPONSE_STATUSES['internal_error']['code']
            );
        }
    }

    /**
     * @Route("coupons/set_used/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function couponsSetUsed(Request $request): JsonResponse
    {
        try {
            parse_str($request->getContent(), $requestObject);

            /** @var CouponsSetUsedRequest $couponsSetUsedRequest */
            $couponsSetUsedRequest = $this->arrayTransformer->fromArray($requestObject, CouponsSetUsedRequest::class);

            // Установка
            $result = $this->manzanaApiService->setCouponsUsed($couponsSetUsedRequest->getCoupons());

            return new JsonResponse(
                ['messages' => $this->arrayTransformer->toArray($result->getMessages())],
                ManzanaApiService::RESPONSE_STATUSES['success']['code']
            );
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                ManzanaApiService::RESPONSE_STATUSES['syntax_error']['code']
            );
        } catch (Throwable $e) {
            $this->log()->critical(__METHOD__ . ' exception: ' . $e->getMessage(), [$e->getTrace()]);

            return new JsonResponse(
                ['error' => 'Что-то пошло не так'],
                ManzanaApiService::RESPONSE_STATUSES['internal_error']['code']
            );
        }
    }
}
