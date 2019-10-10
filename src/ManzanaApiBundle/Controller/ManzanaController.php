<?php

namespace FourPaws\ManzanaApiBundle\Controller;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\ORM\Fields\ExpressionField;
use FourPaws\App\Application;
use FourPaws\ManzanaApiBundle\Dto\Object\Message;
use FourPaws\ManzanaApiBundle\Dto\Request\CouponsAddRequest;
use FourPaws\ManzanaApiBundle\Dto\Request\CouponsIssueRequest;
use FourPaws\ManzanaApiBundle\Dto\Request\CouponsSetUsedRequest;
use FourPaws\ManzanaApiBundle\Dto\Response\CouponsResponse;
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
 * @todo Отрефакторить (вынести логику создания и изменения купонов и выпусков купонов из контроллера)
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
     * @var DataManager
     */
    private $personalCouponUsersManager;

    /**
     * ManzanaController constructor.
     */
    public function __construct()
    {
        $container = Application::getInstance()->getContainer();
        $this->manzanaApiService = Application::getInstance()->getContainer()->get('manzana_api.service');
        $this->arrayTransformer = Application::getInstance()->getContainer()->get(SerializerInterface::class);
        $this->personalCouponUsersManager = $container->get('bx.hlblock.personalcouponusers');
    }

    /**
     * Создает или обновляет выпуск купонов по его ruleCode (коду-названию)
     *
     * @Route("coupons_issue/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postCouponsIssue(Request $request): JsonResponse
    {
        try {
            /** @var CouponsIssueRequest $couponsIssueRequest */
            $couponsIssueRequest = $this->arrayTransformer->fromArray(json_decode($request->getContent(), true), CouponsIssueRequest::class);

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
     * @Route("coupons/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postCoupons(Request $request): JsonResponse
    {
        try {
            /** @var CouponsAddRequest $couponsAddRequest */
            $couponsAddRequest = $this->arrayTransformer->fromArray(json_decode($request->getContent(), true), CouponsAddRequest::class);

            $result = new CouponsResponse();

            $couponsWithManzanaIds = [];
            $coupons = $couponsAddRequest->getCoupons();
            foreach ($coupons as $coupon) {
                $couponsWithManzanaIds[$coupon->getCouponId()] = $coupon;
            }
            $couponsManzanaIds = array_keys($couponsWithManzanaIds);
            if ($couponsManzanaIds) {
                $existingCoupons = $this->personalCouponUsersManager::query()
                    ->setFilter([
                        '=UF_MANZANA_ID' => $couponsManzanaIds
                    ])
                    ->setSelect([
                        new ExpressionField('LAST_ID', 'MAX(%s)', ['ID']),
                        'UF_MANZANA_ID',
                        'UF_DATE_USED',
                        'UF_USED',
                    ])
                    ->setGroup(['UF_MANZANA_ID'])
                    ->setOrder(['LAST_ID' => 'desc'])
                    ->exec()
                    ->fetchAll()
                ;
                $arCouponsManzanaIds = [];
                $couponsToUpdate = [];
                foreach ($existingCoupons as $existingCoupon) {
                    if (!in_array($existingCoupon['UF_MANZANA_ID'],$arCouponsManzanaIds, true)) {
                        if (!$existingCoupon['UF_DATE_USED'] && !$existingCoupon['UF_USED']) {
                            $couponsToUpdate[$existingCoupon['LAST_ID']] = $couponsWithManzanaIds[$existingCoupon['UF_MANZANA_ID']];
                        } else {
                            // Погашенные купоны - не обновляются
                            $result->setMessages(array_merge(
                                $result->getMessages(),
                                [
                                    (new Message())
                                        ->setMessageId($couponsWithManzanaIds[$existingCoupon['UF_MANZANA_ID']]->getMessageId())
                                        ->setMessageStatus('error')
                                        ->setMessageText('Этот купон уже погашен')
                                ]
                            ));
                        }

                        $arCouponsManzanaIds[] = $existingCoupon['UF_MANZANA_ID'];
                    }
                    unset($couponsWithManzanaIds[$existingCoupon['UF_MANZANA_ID']]);
                }
                unset($arCouponsManzanaIds);
            }

            // Обновление купонов
            if (isset($couponsToUpdate) && $couponsToUpdate) {
                $result->setMessages(array_merge(
                    $result->getMessages(),
                    $this->manzanaApiService->updateCoupons($couponsToUpdate)->getMessages()
                ));
            }

            // Добавление купонов
            if ($couponsWithManzanaIds) {
                $result->setMessages(array_merge(
                    $result->getMessages(),
                    $this->manzanaApiService->addCoupons($couponsWithManzanaIds, true)->getMessages()
                ));
            }

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
            /** @var CouponsSetUsedRequest $couponsSetUsedRequest */
            $couponsSetUsedRequest = $this->arrayTransformer->fromArray(json_decode($request->getContent(), true), CouponsSetUsedRequest::class);

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
