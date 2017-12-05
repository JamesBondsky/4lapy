<?php

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;

class CaptchaController extends FOSRestController
{
    /**
     * @see \FourPaws\MobileApiBundle\Dto\Request\CaptchaCreateRequest
     * @Rest\Post(path="/captcha")
     */
    public function createAction()
    {
        /**
         * @todo Сериализация
         */
        /**
         * @todo Валидация
         */

        /**
         * @todo Если валидируемая сущность телефон
         *       Отправка из регистрации - Генерим код подтверждения и отправляем смс
         *       Отправка из редактирования - Генерим код подтверждения и отправляем смс
         *       Отправка из активайии карты - Генерим код подтверждения и отправляем смс
         */

        /**
         * @todo Если валидируемая сущность не телефон
         *       Отправка из редактирования или из активации карты - возвращаем ошибку captcha__email_is_used
         *       Отправка из создания - отправляем письмо с кодом качи
         */
    }

    /**
     * @see \FourPaws\MobileApiBundle\Dto\Request\CaptchaVerifyRequest
     */
    public function verifyAction()
    {
        /**
         * @todo Сериализация
         */

        /**
         * @todo Валидация
         */

        /**
         * @todo Проверка типа сущности
         *       Возможно в рамках Constraint
         */
    }
}
