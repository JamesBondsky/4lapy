<?php

namespace FourPaws\AppBundle\AjaxController;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Type\DateTime;
use CUser;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Exception\JsonResponseException;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\External\ExpertsenderService;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\ProtectorHelper;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
use FourPaws\UserBundle\Repository\FestivalUsersTable;
use FourPaws\UserBundle\Service\UserSearchInterface;
use FourPaws\UserBundle\Service\UserService;
use phpDocumentor\Reflection\Types\Self_;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class LandingController
 *
 * @package FourPaws\AppBundle\AjaxController
 */
class LandingController extends Controller
{

    static $petTypes = [
        'cat' => 'Кошка',
        'smallDog' => 'Собака мелкой породы',
        'otherDog' => 'Собака средней или крупной породы',
    ];

    static $landingSites = ['s2', 's3', 's4', 's5'];

    static $grandinLanding = 'grandin';
    static $royalCaninLanding = 'royal_canin';
    static $festivalLanding = 'festival';
    static $mealfeelLanding = 'mealfeel';

    /** @var AjaxMess */
    private $ajaxMess;

    public function __construct()
    {
        try {
            $container = App::getInstance()->getContainer();
            $this->ajaxMess = $container->get('ajax.mess');
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function addRequestAction(Request $request): JsonResponse
    {
        global $USER;

        try {

            if (!$USER->IsAuthorized()) {
                throw new JsonResponseException($this->ajaxMess->getNotAuthorizedException());
            }

            $arFields = [$request->get('date'), $request->get('sum'), $request->get('surname'), $request->get('name'), $request->get('phone'), $request->get('email'), $request->get('rules')];
            $landingType = $request->get('landingType');

            if ($landingType == self::$grandinLanding || $landingType == self::$mealfeelLanding) {
                $arFields[] = $request->get('petType');
            }

            if (count(array_filter($arFields)) < count($arFields)) {
                throw new JsonResponseException($this->ajaxMess->getEmptyDataError());
            }

            if (!ProtectorHelper::checkToken($request->get(ProtectorHelper::getField(ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD)), ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD)) {
                throw new JsonResponseException($this->ajaxMess->getWrongParamsError());
            }

            if ($landingType == self::$grandinLanding && !in_array($request->get('petType'), array_keys(self::$petTypes))) {
                throw new JsonResponseException($this->ajaxMess->getWrongDataError());
            }

            if ($landingType == self::$grandinLanding && $request->get('sum') < 1800 || $landingType == self::$royalCaninLanding && $request->get('sum') < 1000 || $landingType == self::$mealfeelLanding && $request->get('sum') < 1500) {
                throw new JsonResponseException($this->ajaxMess->getWrongDataError());
            }

            $email = $request->get('email');
            $userId = $USER->GetID();

            $requestIblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::GRANDIN_REQUEST);
            if (in_array($landingType, array_keys([self::$royalCaninLanding, self::$grandinLanding])) || in_array($landingType, array_keys([self::$royalCaninLanding, self::$mealfeelLanding]))) {
                $filter = [
                    'IBLOCK_ID' => $requestIblockId,
                    'CHECK_PERMISSIONS' => 'N',
                    '=CODE' => $landingType . '_requests',
                ];
                $sections = \CIBlockSection::GetList([], $filter, false, ['ID', 'IBLOCK_ID', 'NAME', 'CODE'])->Fetch();
                if (!empty($sections['ID'])) {
                    $sectionId = $sections['ID'];
                }
            }

            $iblockElement = new \CIBlockElement();
            $resultAdd = $iblockElement->Add([
                'IBLOCK_ID' => $requestIblockId,
                'NAME' => 'Заявка ' . implode(' ', [$USER->GetID(), $request->get('surname'), $request->get('name')]),
                'IBLOCK_SECTION_ID' => ($sectionId) ? $sectionId : false,
                'PROPERTY_VALUES' => [
                    'USER' => $userId,
                    'DATE' => $request->get('date'),
                    'SUM' => $request->get('sum'),
                    'SURNAME' => $request->get('surname'),
                    'NAME' => $request->get('name'),
                    'PHONE' => $request->get('phone'),
                    'EMAIL' => $email,
                    'RULES' => $request->get('rules') == 'Y',
                    'PET_TYPE' => self::$petTypes[$request->get('petType')]
                ],
            ]);

            if (!$resultAdd) {
                throw new JsonResponseException($this->ajaxMess->getAddError($iblockElement->LAST_ERROR));
            }

            try {
                $sender = App::getInstance()->getContainer()->get('expertsender.service');
                $sender->sendAfterCheckReg([
                    'userEmail' => $email,
                    'userId' => $userId,
                    'landingType' => $landingType
                ]);
            }
            catch (\Exception $exception)
            {
                $logger = LoggerFactory::create('expertSender');
                $logger->error(sprintf(
                    'Error while sending mail. %s exception: %s',
                    __METHOD__,
                    $exception->getMessage()
                ));
            }

            $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD);
            return JsonSuccessResponse::createWithData('Спасибо за регистрацию', [
                'field' => $token['field'],
                'value' => $token['token'],
            ]);

        } catch (JsonResponseException $e) {

            $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD);
            $token['value'] = $token['token'];
            unset($token['token']);

            return $e->getJsonResponse()->extendData($token);
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ApplicationCreateException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function addFestivalUser(Request $request): JsonResponse
    {
        global $USER;
        $userId = $USER->GetID();

        $phone = $request->get('phone');

        try {
            $isCorrectPhone = false;
            try {
                if (PhoneHelper::isPhone($phone)) {
                    $phone = PhoneHelper::normalizePhone($phone);
                    $isCorrectPhone = true;
                }
            } catch (\Exception $e) {
                $logger = LoggerFactory::create('Festival');
                $logger->info(sprintf(
                    'Неверный номер телефона: ' . $request->get('phone') . '. %s method. %s',
                    __METHOD__,
                    $e
                ));
            }
            if (!$isCorrectPhone) {
                throw new JsonResponseException(JsonErrorResponse::createWithData('Неверный номер телефона'));
            }

            $arFields = [
                $request->get('surname'),
                $request->get('name'),
                $phone,
                $request->get('email'),
                $request->get('rules')
            ];

            if (count(array_filter($arFields)) < count($arFields)) {
                throw new JsonResponseException($this->ajaxMess->getEmptyDataError());
            }

            if (!ProtectorHelper::checkToken($request->get(ProtectorHelper::getField(ProtectorHelper::TYPE_FESTIVAL_REQUEST_ADD)), ProtectorHelper::TYPE_FESTIVAL_REQUEST_ADD)) {
                throw new JsonResponseException($this->ajaxMess->getWrongParamsError());
            }

            $email = $request->get('email');

            $container = App::getInstance()->getContainer();
            /** @var DataManager $festivalUsersDataManager */
            $festivalUsersDataManager = $container->get('bx.hlblock.festivalusersdata');
            $alreadyRegisteredUsers = $festivalUsersDataManager::query()
                ->setFilter([
                    'LOGIC' => 'OR',
                    'UF_PHONE' => $phone,
                    'UF_EMAIL' => $email,
                ])
                ->setSelect([
                    'UF_PHONE',
                    'UF_EMAIL',
                    'UF_FESTIVAL_USER_ID',
                ])
                ->setLimit(2)
                ->exec()
                ->fetchAll();
            if ($alreadyRegisteredUsers) {
                foreach ($alreadyRegisteredUsers as $user)
                {
                    if ($phone === $user['UF_PHONE']) {
                        $phoneDuplicateUserFestivalCode = $user['UF_FESTIVAL_USER_ID'];
                    } elseif ($email === $user['UF_EMAIL']) {
                        $emailDuplicateUserFestivalCode = $user['UF_FESTIVAL_USER_ID'];
                    }
                }
                $registeredUserId = $phoneDuplicateUserFestivalCode ?? ($emailDuplicateUserFestivalCode ?? null);
                throw new JsonResponseException(JsonErrorResponse::createWithData('Такой пользователь уже зарегистрирован' . ($registeredUserId ? '.<br><b>ID участника: ' . $registeredUserId . '</b>' : '')));
            }

            /** @var PersonalOffersService $personalOffersService */
            $personalOffersService = $container->get('personal_offers.service');

            $iblockElement = new \CIBlockElement();
            $festivalUserId = $personalOffersService->generateFestivalUserId();
            if (!$userId) {
                try {
                    /** @var UserService $userService */
                    $userService = App::getInstance()->getContainer()->get(UserSearchInterface::class);
                    $userId = $userService->findOneByPhone($phone)->getId();
                } catch (\Exception $e) {
                }
            }
            if ($userId) {
                $festivalPersonalOfferLinked = false;
                $festivalOffer = $personalOffersService->getActiveOffers(['CODE' => 'festival']);
                if (!$festivalOffer->isEmpty()
                    && ($festivalOfferId = (int)$festivalOffer->first()['ID'])
                ) {
                    try {
                        $coupons = [
                            $festivalUserId => [$userId]
                        ];
                        $personalOffersService->importOffers($festivalOfferId, $coupons);
                        $festivalPersonalOfferLinked = true;
                    } catch (\Exception $e) {
                        $logger = LoggerFactory::create('Festival');
                        $logger->critical(sprintf(
                            'error while creating user\'s promo code. %s method. %s',
                            __METHOD__,
                            $e
                        ));
                    }
                }
                if (!$festivalPersonalOfferLinked) {
                    $logger = LoggerFactory::create('Festival');
                    $logger->critical(sprintf(
                        '%s: couldn\'t create festival coupon for user',
                        __METHOD__
                    ));
                }
            }
            $isfestivalUserAddSuccess = $festivalUsersDataManager::add([
                'UF_USER' => $userId,
                'UF_SURNAME' => $request->get('surname'),
                'UF_NAME' => $request->get('name'),
                'UF_PHONE' => $phone,
                'UF_EMAIL' => $email,
                'UF_RULES' => $request->get('rules') === 'on',
                'UF_FESTIVAL_USER_ID' => $festivalUserId,
                'UF_DATE_CREATED' => new DateTime(),
            ])->isSuccess();

            if (!$isfestivalUserAddSuccess) {
                $logger = LoggerFactory::create('expertSender');
                $logger->error('Не удалось добавить купон ' . $festivalUserId . ' для пользователя ' . $userId . '. ' . __METHOD__ . ' ' . $iblockElement->LAST_ERROR);
                throw new JsonResponseException($this->ajaxMess->getAddError($iblockElement->LAST_ERROR));
            }

            try {
                $barcodeGenerator = new BarcodeGeneratorPNG();
                /** @var ExpertsenderService $sender */
                $sender = App::getInstance()->getContainer()->get('expertsender.service');
                $sender->sendAfterFestivalUserReg([
                    'userEmail' => $email,
                    'coupon' => $festivalUserId,
                    'firstname' => $request->get('name'),
                    'lastname' => $request->get('surname'),
                    'url_img' => 'data:image/png;base64,' . base64_encode($barcodeGenerator->getBarcode($festivalUserId, BarcodeGenerator::TYPE_CODE_128, 2.132310384278889, 127)),
                ]);
            }
            catch (\Exception $exception)
            {
                $logger = LoggerFactory::create('expertSender');
                $logger->error(sprintf(
                    'Error while sending mail with coupon ' . $festivalUserId . ' to address ' . $email . '. %s exception: %s',
                    __METHOD__,
                    $exception->getMessage()
                ));
            }

            $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_FESTIVAL_REQUEST_ADD);
            return JsonSuccessResponse::createWithData('<p><b>ПОЗДРАВЛЯЕМ</b></p>
            <p>Ты – участник Квеста «Хочу в Париж» и почетный гость Фестиваля. На почте уже ждет приглашение с персональным кодом участника и праздничная скидка.</p>', [
                'field' => $token['field'],
                'value' => $token['token'],
            ]);
        } catch (JsonResponseException $e) {

            $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_FESTIVAL_REQUEST_ADD);
            $token['value'] = $token['token'];
            unset($token['token']);

            return $e->getJsonResponse()->extendData($token);
        }
    }

    /**
     * @param Request $request
     */
    public function uploadDrawImageAction(Request $request)
    {
        global $USER;

        try {

            if (!$USER->IsAuthorized()) {
                throw new JsonResponseException($this->ajaxMess->getNotAuthorizedException());
            }

//            if ($landingType == self::$grandinLanding && $request->get('sum') < 1800 || $landingType == self::$royalCaninLanding && $request->get('sum') < 1000 || $landingType == self::$mealfeelLanding && $request->get('sum') < 1500) {
//                throw new JsonResponseException($this->ajaxMess->getWrongDataError());
//            }

            $files = $_FILES;
            $userId = $USER->GetID();
            $userFields = \CUser::GetByID($USER->GetID())->Fetch();

            $requestIblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::HOME_IMAGES);

            $iblockElement = new \CIBlockElement();
            $resultAdd = $iblockElement->Add([
                'IBLOCK_ID' => $requestIblockId,
                'NAME' => 'Заявка ' . implode(' ', [$USER->GetID(), $userFields['LAST_NAME'], $userFields['FIRST_NAME']]),
                'IBLOCK_SECTION_ID' => false,
                'PREVIEW_PICTURE' => $_FILES['PHOTO'],
                'PROPERTY_VALUES' => [
                    'USER_ID' => $userId,
                    'LOGIN' => $userFields['LOGIN'],
                    'FIO' => implode(' ', array_filter([$userFields['LAST_NAME'], $userFields['FIRST_NAME'], $userFields['SECOND_NAME']])),
                    'PHONE' => $userFields['PERSONAL_PHONE'],
                    'EMAIL' => $userFields['EMAIL']
                ],
            ]);

            if (!$resultAdd) {
                throw new JsonResponseException($this->ajaxMess->getAddError($iblockElement->LAST_ERROR));
            }

//            try {
//                $sender = App::getInstance()->getContainer()->get('expertsender.service');
//                $sender->sendAfterCheckReg([
//                    'userEmail' => $email,
//                    'userId' => $userId,
//                    'landingType' => $landingType
//                ]);
//            }
//            catch (\Exception $exception)
//            {
//                $logger = LoggerFactory::create('expertSender');
//                $logger->error(sprintf(
//                    'Error while sending mail. %s exception: %s',
//                    __METHOD__,
//                    $exception->getMessage()
//                ));
//            }

            $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD);
            return JsonSuccessResponse::create('Заявка успешно отправлена!');

        } catch (JsonResponseException $e) {

            return $e->getJsonResponse()->extendData($token);
        }
    }

}
