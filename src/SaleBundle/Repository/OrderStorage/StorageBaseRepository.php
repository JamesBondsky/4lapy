<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Repository\OrderStorage;

use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderPropertyService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class StorageBaseRepository implements StorageRepositoryInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /**
     * @var UserCitySelectInterface
     */
    protected $userCitySelect;

    public function __construct(
        ArrayTransformerInterface $arrayTransformer,
        ValidatorInterface $validator,
        CurrentUserProviderInterface $currentUserProviderInterface,
        UserCitySelectInterface $userCitySelect
    ) {
        $this->arrayTransformer = $arrayTransformer;
        $this->validator = $validator;
        $this->currentUserProvider = $currentUserProviderInterface;
        $this->userCitySelect = $userCitySelect;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function setInitialValues(array $data): array
    {
        $data['UF_FUSER_ID'] = $this->currentUserProvider->getCurrentFUserId();
        if (!$data['PROPERTY_COM_WAY']) {
            $data['PROPERTY_COM_WAY'] = OrderPropertyService::COMMUNICATION_SMS;
        }
        $data['UF_USER_ID'] = 0;
        try {
            $user = $this->currentUserProvider->getCurrentUser();
            $data['UF_USER_ID'] = $user->getId();

            /**
             * Если пользователь не задал контактные данные вручную,
             * заполняем их из его профиля
             */
            if (!$data['PROPERTY_NAME']) {
                $data['PROPERTY_NAME'] = $user->getName();
            }
            if (!$data['PROPERTY_PHONE']) {
                $data['PROPERTY_PHONE'] = $user->getPersonalPhone();
            }
            if (!$data['PROPERTY_EMAIL']) {
                $data['PROPERTY_EMAIL'] = $user->getEmail();
            }

            /**
             * Не показываем капчу авторизованному пользователю
             */
            $data['CAPTCHA_FILLED'] = true;
        } catch (NotAuthorizedException $e) {
            $data['BONUS_SUM'] = 0;
        }

        $selectedCity = $this->userCitySelect->getSelectedCity();
        $data['PROPERTY_CITY'] = $selectedCity['NAME'];
        $data['PROPERTY_CITY_CODE'] = $selectedCity['CODE'];

        return $data;
    }

    /**
     * @param OrderStorage $storage
     * @param string $step
     *
     * @return ConstraintViolationListInterface
     */
    public function validate(OrderStorage $storage, string $step): ConstraintViolationListInterface
    {
        return $this->validator->validate($storage, null, [$step]);
    }

    /**
     * @param OrderStorage $storage
     * @param array $groups
     *
     * @return array
     */
    public function toArray(OrderStorage $storage, array $groups = ['read']): array
    {
        return $this->arrayTransformer->toArray(
            $storage,
            SerializationContext::create()->setGroups($groups)
        );
    }
}
