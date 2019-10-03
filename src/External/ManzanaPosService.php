<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FourPaws\App\Application as App;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\External\Interfaces\ManzanaServiceInterface;
use FourPaws\External\Manzana\Dto\BalanceRequest;
use FourPaws\External\Manzana\Dto\BalanceResponse;
use FourPaws\External\Manzana\Dto\ChequePosition;
use FourPaws\External\Manzana\Dto\ExtendedAttribute;
use FourPaws\External\Manzana\Dto\SoftChequeRequest;
use FourPaws\External\Manzana\Dto\SoftChequeResponse;
use FourPaws\External\Manzana\Exception\ExecuteErrorException;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\Traits\ManzanaServiceTrait;
use FourPaws\Helpers\ArithmeticHelper;
use FourPaws\PersonalBundle\Service\PiggyBankService;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Psr\Log\LoggerAwareInterface;
use Throwable;

/**
 * Class ManzanaService
 *
 * @package FourPaws\External
 */
class ManzanaPosService implements LoggerAwareInterface, ManzanaServiceInterface
{
    public const METHOD_EXECUTE = 'ProcessRequestInfo';

    use ManzanaServiceTrait;

    /**
     * @var SoftChequeResponse[]
     */
    protected $results = [];

    /**
     * @param BasketBase $basket
     * @param string $card
     * @param BasketService $basketService
     *
     * @return SoftChequeRequest
     */
    public function buildRequestFromBasket(
        BasketBase $basket,
        string $card = '',
        BasketService $basketService
    ): SoftChequeRequest {
        $sum = $sumDiscounted = 0.0;

        $request = new SoftChequeRequest();

        $hasItems = false;
        $basketItems = $basket->getBasketItems();
        if (!empty($basketItems)) {
            $productIds = array_map(function ($item) {
                /** @var BasketItem $item */
                return $item->getProductId();
            }, $basketItems
            );
            $offerCollection = (new OfferQuery())->withFilter(['=ID' => $productIds])->exec();
        }

        $iterator = 0;
        /** @var BasketItem $item */
        foreach ($basketItems as $k => $item) {
            if ($basketService->isGiftProduct($item)) {
                continue;
            }

            $xmlId = $basketService->getBasketItemXmlId($item);

            if (null === $xmlId) {
                continue;
            }

            $sum += $item->getBasePrice() * $item->getQuantity();
            $sumDiscounted += $item->getPrice() * $item->getQuantity();

            $item->getBasketCode(); // На всякий случай оставил, чтобы продолжали добавляться порядковые номера в базу

            $chequePosition =
                (new ChequePosition())->setChequeItemNumber(++$iterator)
                    ->setSumm($item->getBasePrice() * $item->getQuantity())
                    ->setQuantity($item->getQuantity())
                    ->setPrice($item->getBasePrice())
                    ->setDiscount(ArithmeticHelper::getPercent(
                        $item->getPrice(),
                        $item->getBasePrice()
                    ))
                    ->setSummDiscounted($item->getPrice() * $item->getQuantity())
                    ->setArticleId($xmlId)
                    ->setChequeItemId($item->getId());

            /**
             * @todo проверить может ли это прийти с предыдущего хита в неактуальном виде
             */
            $signCharge = $item->getPropertyCollection()->getPropertyValues()['HAS_BONUS']['VALUE'];
            if (null === $signCharge) {
                try {
                    $signCharge = $basketService->getBonusAwardingQuantity($item, $basket->getOrder());
                } catch (InvalidArgumentException $e) {
                    $signCharge = $item->getQuantity();
                }
                $basketService->setBasketItemPropertyValue($item, 'HAS_BONUS', $signCharge);

                if (isset($offerCollection)) {
                    /** @var Offer $offer */
                    $offer = $offerCollection->getById($item->getProductId());
                    if ($offer->isShare()) {
                        /** @var PiggyBankService $piggyBankService */
                        $piggyBankService = App::getInstance()->getContainer()->get('piggy_bank.service');

                        $pseudoAction = $offer->getShare()->filter(function (Share $action) use ($piggyBankService) {
                            return (in_array($action->getCode(), [
                                    $piggyBankService::ACTION_CODE,
                                    'royal-canin-vyigray-poezdku',
                                    'novaya-kollektsiya-lezhakov-2019',
                                    'pro-plan-vyigray-iphone-garantirovannye-prizy',
                                    'grandin-12-3kg-sukhogo-korma-dlya-sobak-v-podarok',
                                    'grandin-15-na-sukhoy-korm-dlya-sobak-05'
                                ]) || $action->getPropertySigncharge());
                        });
                        if (!$pseudoAction->isEmpty()) {
                            $basketService->setBasketItemPropertyValue($item, 'IS_PSEUDO_ACTION', BitrixUtils::BX_BOOL_TRUE);
                        }
                    }
                }
            }

//            if ($basketService->getBasketItemPropertyValue($item, 'USE_STAMPS') && ($maxStampsLevel = unserialize($basketService->getBasketItemPropertyValue($item, 'MAX_STAMPS_LEVEL')))) {
                /*$chequePosition->setExtendedAttribute(new ArrayCollection([
                    (new ExtendedAttribute())->setKey($maxStampsLevel['key'])->setValue($maxStampsLevel['value'])
                ]));*/ //TODO использовать, когда сделаем сохранение параметра первого запроса мягкого чека (до запроса на применение марок)

                // Применение скидок пока реализовано на нашей стороне (исходя из ключей уровней, присланных из Manzana). см. \FourPaws\SaleBundle\Discount\Utils\Detach\Adder::processOrder
//            }

            $chequePosition->setSignCharge((bool)$signCharge ? 1 : 0);

            $request->addItem($chequePosition);
            $hasItems = true;
        }

        if (!$hasItems) {
            return $request;
        }

        $request->setSumm($sum)
            ->setSummDiscounted($sumDiscounted)
            ->setDiscount(ArithmeticHelper::getPercent($sumDiscounted, $sum))
            ->setPaidByBonus(floor($basket->getPrice()));

        if ($card) {
            $request->setCardByNumber($card);
        }

        return $request;
    }

    /**
     * Запрос на обработку мягкого чека с оплатой баллами
     *
     * Точки входа:
     *
     * - переход на шаг 3 оформления заказа
     *
     * @param SoftChequeRequest $chequeRequest
     *
     * @throws ExecuteException
     *
     * @return SoftChequeResponse
     */
    public function processCheque(SoftChequeRequest $chequeRequest): SoftChequeResponse
    {
        return $this->execute($chequeRequest);
    }

    /**
     * Запрос на обработку мягкого чека с привязанными купонами
     *
     * Точки входа:
     *
     * - ввод промо-кода и клик на кнопку «Применить»
     *
     * При примененном промокоде:
     * - Переход пользователя в корзину
     * - Изменение количества единиц товара в корзине
     * - Удаление товара из корзины
     * - Добавление товара в корзину
     * - Подтверждение изменения выбранного населенного пункта
     * - Установка чекбокса «Забрать через час, за исключением»
     * - Подтверждение изменения выбранного населенного пункта
     *
     * @param SoftChequeRequest $chequeRequest
     * @param string $coupon
     *
     * @throws ExecuteException
     *
     * @return SoftChequeResponse
     */
    public function processChequeWithCoupons(SoftChequeRequest $chequeRequest, string $coupon): SoftChequeResponse
    {
        $chequeRequest->addCoupon($coupon);

        return $this->execute($chequeRequest);
    }

    /**
     * Запрос на обработку мягкого чека без оплаты баллами
     *
     * Точки входа:
     *
     * - Переход на спасибо-страницу после успешного оформления заказа
     *
     * При непримененном промокоде:
     *
     * - Переход пользователя в корзину
     * - Изменение количества единиц товара в корзине
     * - Удаление товара из корзины
     * - Добавление товара в корзину
     * - Подтверждение изменения выбранного населенного пункта
     * - Установка чекбокса «Забрать через час, за исключением»
     * - Подтверждение изменения выбранного населенного пункта
     *
     * @param SoftChequeRequest $chequeRequest
     *
     * @throws ExecuteException
     *
     * @return SoftChequeResponse
     */
    public function processChequeWithoutBonus(SoftChequeRequest $chequeRequest): SoftChequeResponse
    {
        $chequeRequest->setPaidByBonus(0);

        return $this->execute($chequeRequest);
    }

    /**
     * @param SoftChequeRequest $chequeRequest
     */
    protected function prepareRequest(SoftChequeRequest $chequeRequest)
    {
        $requestId = $this->generateRequestId();

        $chequeRequest->setBusinessUnit($this->parameters['business_unit'])
            ->setOrganization($this->parameters['organization'])
            ->setPos($this->parameters['pos'])->setNumber($requestId)
            ->setDatetime(new DateTimeImmutable())
            ->setRequestId($requestId);

        $chequeRequest->getItems()->forAll(
            function (
                /** @noinspection PhpUnusedParameterInspection */
                $k,
                ChequePosition $item
            ) use ($requestId) {
                $item->setChequeId($requestId);
            });
    }

    /**
     * @param BalanceRequest $balanceRequest
     * @throws Exception
     */
    protected function prepareBalanceRequest(BalanceRequest $balanceRequest)
    {
        $requestId = $this->generateRequestId();

        $balanceRequest->setBusinessUnit($this->parameters['business_unit'])
            ->setOrganization($this->parameters['organization'])
            ->setPos($this->parameters['pos'])
            ->setDatetime(new DateTimeImmutable())
            ->setRequestId($requestId);
    }

    /**
     * @param SoftChequeRequest $chequeRequest
     *
     * @return array
     */
    protected function buildParametersFromRequest(SoftChequeRequest $chequeRequest): array
    {
        $this->prepareRequest($chequeRequest);

        return [
                'request_options' =>
                    [
                        'request' => [
                            'Requests' => [
                                $chequeRequest::ROOT_NAME => $this->serializer->toArray($chequeRequest),
                            ],
                        ],
                        'orgName' => $this->parameters['organization_name'],
                    ],
            ];
    }

    /**
     * @param BalanceRequest $balanceRequest
     * @return array
     * @throws Exception
     */
    protected function buildParametersFromBalanceRequest(BalanceRequest $balanceRequest): array
    {
        $this->prepareBalanceRequest($balanceRequest);

        return [
                'request_options' =>
                    [
                        'request' => [
                            'Requests' => [
                                $balanceRequest::ROOT_NAME => $this->serializer->toArray($balanceRequest),
                            ],
                        ],
                        'orgName' => $this->parameters['organization_name'],
                    ],
            ];
    }

    /**
     * @param $rawResult
     *
     * @return SoftChequeResponse
     */
    protected function buildResponseFromRawResponse($rawResult): SoftChequeResponse
    {
        $rawResult = $rawResult->ProcessRequestInfoResult->Responses->ChequeResponse;

        $rawResult = \json_decode(\json_encode($rawResult), true);

        /**
         * Если в запросе был один товар, то возвращается неверная структура
         */
        if (!empty($rawResult['Item']) && !isset($rawResult['Item'][0])) {
            $rawResult['Item'] = [
                $rawResult['Item'],
            ];
        }

        /**
         * Если в запросе был один уровень скидки за марки, то возвращается неверная структура
         */
        foreach ($rawResult['Item'] as $key => $item) {
            if (!empty($item['ExtendedAttribute']) && !isset($item['ExtendedAttribute'][0])) {
                $rawResult['Item'][$key]['ExtendedAttribute'] = [
                    $item['ExtendedAttribute'],
                ];
            }
        }

        return $this->serializer->fromArray($rawResult, SoftChequeResponse::class);
    }

    /**
     * @param $rawResult
     *
     * @return BalanceResponse
     */
    protected function buildResponseFromRawBalanceResponse($rawResult): BalanceResponse
    {
        $rawResult = $rawResult->ProcessRequestInfoResult->Responses->BalanceResponse;

        $rawResult = \json_decode(\json_encode($rawResult), true);

        return $this->serializer->fromArray($rawResult, BalanceResponse::class);
    }

    /**
     * @param SoftChequeRequest $chequeRequest
     * @param bool              $noCache
     *
     * @return SoftChequeResponse
     * @throws ExecuteException
     */
    public function execute(SoftChequeRequest $chequeRequest, bool $noCache = false): SoftChequeResponse
    {
        $items = [];
        /** @var ChequePosition $item */
        foreach ($chequeRequest->getItems() as $item) {
            $items[$item->getChequeItemId()] = $item->getQuantity();
        }
        asort($items);

        $cacheKey = \json_encode(['items' => $items, 'bonus' => floor($chequeRequest->getPaidByBonus())]);
        if ($noCache || !$this->results[$cacheKey]) {
            try {
                $userId = 0;

                $container = App::getInstance()->getContainer();

                try {
                    /** @var UserService $userCurrentUserService */
                    $userCurrentUserService = $container->get(CurrentUserProviderInterface::class);
                    $currentUser = $userCurrentUserService->getCurrentUser();
                    $userId = $currentUser->getId();
                } catch (Exception $e) {}

                $arguments = $this->buildParametersFromRequest($chequeRequest);

                $this->logger->info('Manzana query pos', [
                    'user_id' => $userId,
                    'arguments' => $arguments,
                ]);
                $result = $this->buildResponseFromRawResponse(
                    $this->client->call(
                        self::METHOD_EXECUTE,
                        $arguments
                    )
                );
            } catch (Exception $e) {
                try {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $detail = $e->detail->details->description;
                } catch (Throwable $e) {
                    $detail = 'none';
                }

                throw new ExecuteException(
                    \sprintf(__METHOD__ . '. Execute error: %s, detail: %s', $e->getMessage(), $detail),
                    $e->getCode(),
                    $e
                );
            }

            if (!$noCache) {
                $this->results[$cacheKey] = $result;
            }
        } else {
            $result = $this->results[$cacheKey];
        }

        if ($result->isErrorResponse() && $result->getItems()->isEmpty()) {
            throw new ExecuteErrorException($result->getMessage(), $result->getReturnCode());
        }

        return $result;
    }

    /**
     * @param BalanceRequest $balanceRequest
     * @param bool $noCache
     *
     * @return BalanceResponse
     * @throws ExecuteErrorException
     * @throws ExecuteException
     */
    public function executeBalanceRequest(BalanceRequest $balanceRequest, bool $noCache = false): BalanceResponse
    {
        $card = $balanceRequest->getCard();
        if (!$card) {
            throw new ExecuteErrorException(__METHOD__ . '. Не задан номер карты');
        }
        $cacheKey = \json_encode(['cardNumber' => $card->getNumber()]);
        if ($noCache || !$this->results[$cacheKey]) {
            try {
                $result = $this->buildResponseFromRawBalanceResponse(
                    $this->client->call(
                        self::METHOD_EXECUTE,
                        $this->buildParametersFromBalanceRequest($balanceRequest)
                    )
                );
            } catch (Exception $e) {
                try {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $detail = $e->detail->details->description;
                } catch (Throwable $e) {
                    $detail = 'none';
                }

                throw new ExecuteException(
                    \sprintf(__METHOD__ . '. Execute error: %s, detail: %s', $e->getMessage(), $detail),
                    $e->getCode(),
                    $e
                );
            }

            if (!$noCache) {
                $this->results[$cacheKey] = $result;
            }
        } else {
            $result = $this->results[$cacheKey];
        }

        if ($result->isErrorResponse()) {
            throw new ExecuteErrorException($result->getMessage(), $result->getReturnCode());
        }

        return $result;
    }

    /**
     * @return int
     */
    protected function generateRequestId(): int
    {
        return (int)((\microtime(true) * 1000) . random_int(1000, 9999));
    }
}
