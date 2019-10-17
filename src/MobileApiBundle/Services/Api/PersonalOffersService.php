<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\ObjectPropertyException;
use CUser;
use Exception;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\ArgumentException;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorPNG;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\External\ExpertsenderService;
use Picqer\Barcode\Exceptions\BarcodeException;
use LinguaLeo\ExpertSender\ExpertSenderException;
use Adv\Bitrixtools\Exception\IblockNotFoundException;
use FourPaws\External\Exception\ExpertsenderServiceException;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\External\Exception\ExpertsenderServiceApiException;
use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FourPaws\PersonalBundle\Service\PersonalOffersService as PersonalBundlePersonalOffersService;

class PersonalOffersService
{
    /**
     * @var ApiUserSessionRepository $apiUserSessionRepository
     */
    private $apiUserSessionRepository;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var PersonalBundlePersonalOffersService $personalOffersService
     */
    private $personalOffersService;

    /** @var ExpertsenderService $expertsenderService */
    private $expertsenderService;

    /** @var AjaxMess */
    private $ajaxMess;

    /**
     * PersonalOffersService constructor.
     * @param ApiUserSessionRepository $apiUserSessionRepository
     * @param TokenStorageInterface $tokenStorage
     * @param PersonalBundlePersonalOffersService $personalOffersService
     * @param ExpertsenderService $expertsenderService
     */
    public function __construct(
        ApiUserSessionRepository $apiUserSessionRepository,
        TokenStorageInterface $tokenStorage,
        PersonalBundlePersonalOffersService $personalOffersService,
        ExpertsenderService $expertsenderService
    ) {
        $this->apiUserSessionRepository = $apiUserSessionRepository;
        $this->tokenStorage = $tokenStorage;
        $this->personalOffersService = $personalOffersService;
        $this->expertsenderService = $expertsenderService;
    }

    /**
     * @return array
     */
    public function getPersonalOffers()
    {
        $session = $this->apiUserSessionRepository->findByToken($this->tokenStorage->getToken()->getCredentials());
        $userId = $session->getUserId();
        if (!$userId) {
            return [
                'success' => false,
                'error'   => [
                    'code'    => 0,
                    'message' => 'Пользователь не авторизован'
                ]
            ];
        }
        try {
            $result = [
                'success' => true,
                'data'    => [
                    'personal_offers' => $this->personalOffersService->getActiveUserCouponsEx($userId),
                ]
            ];
        } catch (BarcodeException|IblockNotFoundException|ArgumentException|LoaderException|SystemException|InvalidArgumentException $exception) {
            $result = [
                'success' => false,
                'error'   => [
                    'code'    => $exception->getCode(),
                    'message' => $exception->getMessage()
                ]
            ];
        }

        return $result;
    }

    /**
     * @param string $email
     * @param string $promocode
     * @return array
     */
    public function sendEmail(string $email, string $promocode): array
    {
        $session = $this->apiUserSessionRepository->findByToken($this->tokenStorage->getToken()->getCredentials());
        $userId = $session->getUserId();

        try {
            if (!$userId) {
                throw new Exception('Пользователь не авторизирован!');
            }

            if (!$email) {
                throw new Exception('Email пользователя не задан!');
            }

            if (!$promocode) {
                throw new Exception('Промокод не задан!');
            }

            $curUser = CUser::GetByID($userId)->Fetch();

            $barcodeGenerator = new BarcodeGeneratorPNG();

            /** в случае если в email пользователя указана не корректная почта, то переписываем её почтой на которую отправляется купон */
            if (false
                || !$curUser['EMAIL']
                || $curUser['EMAIL'] == 'no@mail.ru'
                || mb_strpos($curUser['EMAIL'], '@register.phone') !== false
                || mb_strpos($curUser['EMAIL'], '@fastorder.ru') !== false
            ) {
                $user = new CUser;
                $user->Update($userId, [
                    'EMAIL' => $email,
                ]);
            }

            $offerFields = $this->personalOffersService->getOfferFieldsByPromoCode($promocode);

            if ($offerFields->count() == 0) {
                throw new Exception('Купон по промокоду не найден');
            }

            $couponDescription = $offerFields->get('PREVIEW_TEXT');
            $couponDateActiveTo = $offerFields->get('custom_date_active_to');
            $discountValue = $offerFields->get('PROPERTY_DISCOUNT_VALUE');

            $this->expertsenderService->sendPersonalOfferCouponEmail(
                $userId,
                $curUser['NAME'],
                $email,
                $promocode,
                'data:image/png;base64,' . base64_encode($barcodeGenerator->getBarcode($promocode, BarcodeGenerator::TYPE_CODE_128, 2.132310384278889, 127)),
                $couponDescription,
                $couponDateActiveTo,
                $discountValue
            );
            $result = [
                'success' => true,
                'data'    => [
                    'message' => 'Купон успешно отправлен на почту'
                ]
            ];
        } catch (Exception|BarcodeException|IblockNotFoundException|InvalidArgumentException|LoaderException|ExpertsenderServiceApiException|ExpertsenderServiceException|ExpertSenderException $exception) {
            $result = [
                'success' => false,
                'error'   => [
                    'code'    => $exception->getCode(),
                    'message' => $exception->getMessage()
                ]
            ];
        }

        return $result;

    }

    /**
     * @param string $orderID
     *
     * @return array|null
     */
    public function bindUnreservedDobrolapCoupon(string $orderID = ''): ?array
    {
        if(!$orderID){
            return [
                'success' => false,
                'message' => 'Номер заказа не передан'
            ];
        }
        $session = $this->apiUserSessionRepository->findByToken($this->tokenStorage->getToken()->getCredentials());
        $userId = $session->getUserId();
        try {
            return $this->personalOffersService->bindDobrolapRandomCoupon($userId, $orderID, false, false);
        } catch (IblockNotFoundException |ObjectPropertyException| ArgumentException |LoaderException |SystemException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage() . ' [' . $e->getCode() . ']'
            ];
        }
    }
}
