<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\UserTable;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Object\ChangeCardProfile;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Exception\RuntimeException;
use FourPaws\UserBundle\Service\UserService as AppUserService;
use FourPaws\PersonalBundle\Entity\UserBonus as AppUserBonus;
use FourPaws\External\ManzanaService as AppManzanaService;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\MobileApiBundle\Services\Api\UserService as ApiUserService;


class CardService
{
    /**
     * @var AppManzanaService
     */
    private $appManzanaService;

    /**
     * @var AppUserService
     */
    private $appUserService;

    /**
     * @var AppUserBonus
     */
    private $appUserBonus;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ApiUserService
     */
    private $apiUserService;

    public function __construct(
        AppManzanaService $appManzanaService,
        AppUserService $appUserService,
        AppUserBonus $appUserBonus,
        UserRepository $userRepository,
        ApiUserService $apiUserService
    )
    {
        $this->appManzanaService = $appManzanaService;
        $this->appUserService = $appUserService;
        $this->appUserBonus = $appUserBonus;
        $this->userRepository = $userRepository;
        $this->apiUserService = $apiUserService;
    }

    /**
     * @param int $cardNumber
     *
     * @return Response
     */
    public function isActive($cardNumber): Response
    {
        $activated = UserTable::query()
                ->addFilter('UF_DISCOUNT_CARD', $cardNumber)
                ->exec()
                ->getSelectedRowsCount() > 0;
        $cardResponse = new Response\CardActivatedResponse(
            $activated,
            $activated ? 'Карта уже привязана к другому аккаунту. Пожалуйста, используйте другую карту' : ''
        );

        $apiResponse = new Response($cardResponse);
        if ($activated) {
            $apiResponse->addError(new Error(42, 'Данная карта уже привязана'));
        }
        return $apiResponse;
    }

    /**
     * Задача: зная номер карты, получить по ней данные из МЛ и вернуть их в МП
     * Обязательно сделать проверку на совпадение номера телефона (phone)
     *
     * @param $cardNumber
     * @return ChangeCardProfile
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\External\Manzana\Exception\CardNotFoundException
     * @throws \Exception
     */
    public function getCardDataFromManzana($cardNumber): ChangeCardProfile
    {
        $user = $this->appUserService->getCurrentUser();
        $card = $this->appManzanaService->searchCardByNumber($cardNumber);

        $userPhone = preg_replace("/^(?:.*)(?|\((\d{3})\)(\d{3})|\((\d{4})\)(\d{2})|(\d{3})(\d{3}))(\d{2})(\d{2})$/", "$1$2$3$4", $user->getPersonalPhone());
        $cardPhone = preg_replace("/^(?:.*)(?|\((\d{3})\)(\d{3})|\((\d{4})\)(\d{2})|(\d{3})(\d{3}))(\d{2})(\d{2})$/", "$1$2$3$4", $card->phone);

        if (!empty($cardPhone) && $userPhone != $cardPhone) {
            throw new \Exception("Не удалось получить данные по бонусной карте: номер телефона авторизованного пользователя $userPhone и номер телефона карты $cardPhone не совпадают.");
        }

        $cardProfile = (new ChangeCardProfile())
            ->setNewCardNumber($cardNumber)
            ->setLastName($card->lastName)
            ->setFirstName($card->firstName)
            ->setPhone($userPhone);

        if ($card->birthDate) {
            $birthDate = (new \DateTime())->setTimestamp($card->birthDate->getTimestamp());
            $cardProfile->setBirthDate($birthDate);
        }

        if (stristr($card->email, '@register.phone') === false) {
            $cardProfile->setEmail($card->email);
        }

        if ($card->secondName) {
            $cardProfile->setSecondName($card->secondName);
        }

        return $cardProfile;
    }

    /**
     * Задача: проверить капчу, и если все гут - апдейтить юзера в БД
     * @param ChangeCardProfile $cardProfile
     * @param $captchaId
     * @param $captchaValue
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     */
    public function changeCardConfirmPin(ChangeCardProfile $cardProfile, $captchaId, $captchaValue)
    {
        $user = $this->appUserService->getCurrentUser();
        $oldCard = $user->getDiscountCardNumber();
        $newCard = $cardProfile->getNewCardNumber();
        // 1. проверяем капчу
        if (!$GLOBALS['APPLICATION']->CaptchaCheckCode($captchaValue, $captchaId)) {
            throw new RuntimeException('Некорректный код');
        }
        // 2. проверяем, нет ли уже в базе такого номера карты
        if ($this->isActive($cardProfile->getNewCardNumber())) {
            throw new RuntimeException('Карта уже используется');
        }
        // 3. заменяем карту в манзане
        $this->appManzanaService->changeCard($oldCard, $newCard);
        // 4. заменяем данные в битриксовом профиле пользователя
        $user->setDiscountCardNumber($newCard);
        $this->userRepository->update($user);
    }
}
