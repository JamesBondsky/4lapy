<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Validation;

use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderStorageService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OrderPaymentSystemValidator extends ConstraintValidator
{
    /**
     * @var OrderStorageService
     */
    protected $orderStorageService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    public function __construct(OrderStorageService $orderStorageService, DeliveryService $deliveryService)
    {
        $this->orderStorageService = $orderStorageService;
    }

    /**
     * @param mixed $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$entity instanceof OrderStorage || !$constraint instanceof OrderPaymentSystem) {
            return;
        }

        /**
         * Способ оплаты не выбран
         */
        if (!$entity->getPaymentId()) {
            $this->context->addViolation($constraint->paymentSystemMessage);

            return;
        }

        /**
         * Проверка, что выбран верный способ оплаты
         */
        $availablePayments = $this->orderStorageService->getAvailablePayments($entity);

        $selected = false;
        foreach ($availablePayments as $payment) {
            if ($entity->getPaymentId() === (int)$payment['PAY_SYSTEM_ID']) {
                $selected = true;
                break;
            }
        }
        if (!$selected) {
            $this->context->addViolation($constraint->paymentSystemMessage);

            return;
        }
    }
}
