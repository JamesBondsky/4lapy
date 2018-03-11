<?php

namespace FourPaws\External;

use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use FourPaws\Catalog\Model\Offer;
use FourPaws\External\Interfaces\ManzanaServiceInterface;
use FourPaws\External\Manzana\Dto\ChequePosition;
use FourPaws\External\Manzana\Dto\SoftChequeRequest;
use FourPaws\External\Manzana\Dto\SoftChequeResponse;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\Traits\ManzanaServiceTrait;
use FourPaws\Helpers\ArithmeticHelper;
use Psr\Log\LoggerAwareInterface;

/**
 * Class ManzanaService
 *
 * @package FourPaws\External
 */
class ManzanaPosService implements LoggerAwareInterface, ManzanaServiceInterface
{
    const METHOD_EXECUTE = 'ProcessRequestInfo';

    use ManzanaServiceTrait;

    /**
     * @param BasketBase $basket
     * @param string $card
     *
     * @return SoftChequeRequest
     */
    public function buildRequestFromBasket(
        BasketBase $basket,
        string $card = ''
    ): SoftChequeRequest {
        $sum = $sumDiscounted = 0.0;

        $request = new SoftChequeRequest();

        $iterator = 0;
        /** @var BasketItem $item */
        foreach ($basket->getBasketItems() as $k => $item) {
            $sum += $item->getBasePrice() * $item->getQuantity();
            $sumDiscounted += $item->getPrice() * $item->getQuantity();

            $xmlId = $item->getField('PRODUCT_XML_ID');

            if (strpos($xmlId, '#')) {
                $xmlId = explode('#', $xmlId)[1];
            }

            $chequePosition =
                (new ChequePosition())->setChequeItemNumber($iterator++)
                    ->setSumm($item->getBasePrice() * $item->getQuantity())
                    ->setQuantity($item->getQuantity())
                    ->setPrice($item->getBasePrice())
                    ->setDiscount(ArithmeticHelper::getPercent($item->getPrice(),
                        $item->getBasePrice()))
                    ->setSummDiscounted($item->getPrice() * $item->getQuantity())
                    ->setArticleId($xmlId)
                    ->setChequeItemId($item->getId());

            /**
             * @todo add SignCharge=0 (BonusBuy)
             */
            if ((int)$chequePosition->getDiscount() === 3) {
                $chequePosition->setSignCharge(0);
            }

            $request->addItem($chequePosition);
        }

        $request->setSumm($sum)
            ->setSummDiscounted($sumDiscounted)
            ->setDiscount(ArithmeticHelper::getPercent($sumDiscounted, $sum));

        if ($card) {
            $request->setCardByNumber($card);
        }

        return $request;
    }

    public function buildRequestFromItem(Offer $offer, string $card = '', int $quantity = 1)
    {
        $sum = $sumDiscounted = 0.0;

        $request = new SoftChequeRequest();

        $sum += $offer->getPrice() * $quantity;
        $sumDiscounted += $offer->getPrice() * $quantity;

        $xmlId = $offer->getXmlId();

        if (strpos($xmlId, '#')) {
            $xmlId = explode('#', $xmlId)[1];
        }

        $chequePosition =
            (new ChequePosition())->setChequeItemNumber(1)
                ->setSumm($offer->getPrice() * $quantity)
                ->setQuantity($quantity)
                ->setPrice($offer->getPrice())
                ->setDiscount(ArithmeticHelper::getPercent($offer->getPrice(),
                    $offer->getOldPrice()))
                ->setSummDiscounted($offer->getPrice() * $quantity)
                ->setArticleId($xmlId)
                ->setChequeItemId($offer->getId());

        /**
         * @todo add SignCharge=0 (BonusBuy)
         */
        if ((int)$chequePosition->getDiscount() === 3) {
            $chequePosition->setSignCharge(0);
        }

        $request->addItem($chequePosition);

        $request->setSumm($sum)
            ->setSummDiscounted($sumDiscounted)
            ->setDiscount(ArithmeticHelper::getPercent($sumDiscounted, $sum));

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
     * @param string            $coupon
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
     *
     * @return SoftChequeResponse
     *
     * @throws ExecuteException
     */
    protected function execute(SoftChequeRequest $chequeRequest): SoftChequeResponse
    {
        $requestId = $this->generateRequestId();

        $chequeRequest->setBusinessUnit($this->parameters['business_unit'])
            ->setOrganization($this->parameters['organization'])
            ->setPos($this->parameters['pos'])->setNumber($requestId)
            ->setDatetime(new \DateTimeImmutable())
            ->setRequestId($requestId);

        $chequeRequest->getItems()->forAll(function ($k, ChequePosition $item) use ($requestId) {
            $item->setChequeId($requestId);
        });

        try {
            $arguments = [
                'request' => [
                    'Requests' => [
                        $chequeRequest::ROOT_NAME => $this->serializer->toArray($chequeRequest),
                    ],
                ],
                'orgName' => $this->parameters['organization_name'],
            ];

            $rawResult = $this->client->call(self::METHOD_EXECUTE, ['request_options' => $arguments]);
            $rawResult = (array)$rawResult->ProcessRequestInfoResult->Responses->ChequeResponse;
            if ($rawResult['Item']) {
                if (is_array($rawResult['Item'])) {

                    foreach ($rawResult['Item'] as &$item) {
                        $item = (array)$item;
                    }

                    unset($item);
                } elseif ($rawResult['Item'] instanceof \stdClass) {
                    $rawResult['Item'] = [(array)$rawResult['Item']];
                }
            }

            $result = $this->serializer->fromArray($rawResult, SoftChequeResponse::class);
        } catch (\Exception $e) {
            try {
                $detail = $e->detail->details->description;
            } catch (\Throwable $e) {
                $detail = 'none';
            }

            throw new ExecuteException(sprintf('Execute error: %s, detail: %s', $e->getMessage(), $detail),
                $e->getCode(),
                $e);
        }

        return $result;
    }

    /**
     * @return int
     */
    protected function generateRequestId(): int
    {
        return (int)((microtime(true) * 1000) . random_int(1000, 9999));
    }
}
