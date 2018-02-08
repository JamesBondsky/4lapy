<?php

namespace FourPaws\External;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\External\Interfaces\ManzanaServiceInterface;
use FourPaws\External\Manzana\Dto\Coupon;
use FourPaws\External\Manzana\Dto\SoftChequeRequest;
use FourPaws\External\Manzana\Dto\SoftChequeResponse;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\Traits\ManzanaServiceTrait;
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
     * @param SoftChequeRequest $chequeRequest
     *
     * @return SoftChequeResponse
     *
     * @throws ExecuteException
     */
    public function execute(SoftChequeRequest $chequeRequest) : SoftChequeResponse
    {
        $chequeRequest->setBusinessUnit($this->parameters['business_unit'])
                      ->setOrganization($this->parameters['organization'])
                      ->setPos($this->parameters['pos'])
                      ->setDatetime(new \DateTimeImmutable())
                      ->setRequestId($this->generateRequestId());
        
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
    public function processCheque(SoftChequeRequest $chequeRequest) : SoftChequeResponse
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
    public function processChequeWithCoupons(SoftChequeRequest $chequeRequest, string $coupon) : SoftChequeResponse
    {
        $chequeRequest->getCoupons()->setCoupons(new ArrayCollection([(new Coupon())->setNumber($coupon)]));
        
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
    public function processChequeWithoutBonus(SoftChequeRequest $chequeRequest) : SoftChequeResponse
    {
        $chequeRequest->setPaidByBonus(0);
        
        return $this->execute($chequeRequest);
    }
    
    /**
     * @return int
     */
    protected function generateRequestId() : int
    {
        return (int)((microtime(true) * 1000) . random_int(1000, 9999));
    }
}
