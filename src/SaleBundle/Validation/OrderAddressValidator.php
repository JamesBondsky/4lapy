<?php

namespace FourPaws\SaleBundle\Validation;

use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OrderAddressValidator extends ConstraintValidator
{
    /**
     * @var OrderStorageService
     */
    protected $orderStorageService;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    public function __construct(OrderStorageService $orderStorageService, CurrentUserProviderInterface $currentUserProvider)
    {
        $this->orderStorageService = $orderStorageService;
        $this->currentUserProvider = $currentUserProvider;
    }

    /**
     * @param mixed $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$entity instanceof OrderStorage || !$constraint instanceof OrderAddress) {
            return;
        }

        /** @var DeliveryService $deliveryService */
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');

        /**
         * Не выбрана доставка - не проверяем. Должен ругнуться другой валидатор
         */
        if (!$deliveryId = $entity->getDeliveryId()) {
            return;
        }

        /**
         * Проверка, что выбрана доступная доставка
         * Если выбрана недоступная - не проверяем
         */
        $deliveryMethods = $this->orderStorageService->getDeliveries($entity);
        $delivery = null;
        foreach ($deliveryMethods as $deliveryMethod) {
            if ($deliveryId === $deliveryMethod->getDeliveryId()) {
                $delivery = $deliveryMethod;
                break;
            }
        }
        if (!$delivery) {
            return;
        }

        /**
         * Если выбранный способ доставки - не курьерская доставка, то не проверяем.
         */
        if (!$deliveryService->isDelivery($delivery)) {
            return;
        }

        /**
         * Пользователь авторизован и выбрал адрес из списка
         */
        if ($entity->getUserId() && $entity->getAddressId()) {
            /** @var AddressService $addressService */
            $addressService = Application::getInstance()->getContainer()->get('address.service');
            $found = true;
            try {
                $address = $addressService->getById($entity->getAddressId());
                if (($address->getUserId() != $entity->getUserId()) ||
                    ($address->getLocation() != $entity->getCityCode())
                ) {
                    $found = false;
                }
            } catch (NotFoundException $e) {
                $found = false;
            }

            if (!$found) {
                $this->context->addViolation($constraint->addressMessage);
            }
        } else {
            /**
             * Адрес введен вручную
             */
            if (!$entity->getHouse()) {
                $this->context->addViolation($constraint->houseMessage);
            }

            if (!$entity->getStreet()) {
                $this->context->addViolation($constraint->streetMessage);
            }
        }
    }
}
