<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\ArgumentException;
use Picqer\Barcode\Exceptions\BarcodeException;
use Adv\Bitrixtools\Exception\IblockNotFoundException;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
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

    /**
     * PersonalOffersService constructor.
     * @param ApiUserSessionRepository $apiUserSessionRepository
     * @param TokenStorageInterface $tokenStorage
     * @param PersonalBundlePersonalOffersService $personalOffersService
     */
    public function __construct(
        ApiUserSessionRepository $apiUserSessionRepository,
        TokenStorageInterface $tokenStorage,
        PersonalBundlePersonalOffersService $personalOffersService
    ) {
        $this->apiUserSessionRepository = $apiUserSessionRepository;
        $this->tokenStorage = $tokenStorage;
        $this->personalOffersService = $personalOffersService;
    }

    /**
     * @return array
     */
    public function getPersonalOffers()
    {
        $session = $this->apiUserSessionRepository->findByToken($this->tokenStorage->getToken()->getCredentials());
        $userId = $session->getUserId();
        if (!$userId) {
            return [];
        }
        try {
            $result = $this->personalOffersService->getActiveUserCouponsEx($userId);
        } catch (BarcodeException|IblockNotFoundException|ArgumentException|LoaderException|SystemException|InvalidArgumentException $exception) {
            $result = [
                'error' => [
                    'code'    => $exception->getCode(),
                    'message' => $exception->getMessage()
                ]
            ];
        }

        return $result;
    }
}
