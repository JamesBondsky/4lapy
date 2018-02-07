<?php

namespace FourPaws\External;

use FourPaws\External\Interfaces\ManzanaServiceInterface;
use FourPaws\External\Manzana\Exception\AuthenticationException;
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
    use ManzanaServiceTrait;
    
    /**
     * @param string $contract
     * @param array  $parameters
     *
     * @return string
     *
     * @throws AuthenticationException
     * @throws ExecuteException
     */
    protected function execute(string $contract, array $parameters = []) : string
    {
        try {
            $arguments = [
                'contractName' => $contract,
                'parameters'   => $parameters,
            ];
            
            $result = $this->client->call(self::METHOD_EXECUTE, ['request_options' => $arguments]);
            
            $result = $result->ExecuteResult->Value;
        } catch (\Exception $e) {
            unset($this->sessionId);
            
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
     */
    public function processCheque()
    {
        
        $this->execute('', []);
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
     */
    public function processChequeWithCoupons()
    {
        
        $this->execute('', []);
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
     */
    public function processChequeWithoutBonus()
    {
        $this->execute('', []);
    }
}
