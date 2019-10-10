<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Grid\Declension;
use CEvent;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\External\Manzana\Exception\ExecuteErrorException;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Request\LoginExistRequest;
use FourPaws\MobileApiBundle\Dto\Request\LoginRequest;
use FourPaws\MobileApiBundle\Dto\Request\PostUserInfoRequest;
use FourPaws\MobileApiBundle\Dto\Request\VerificationCodeSendByEmailRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Services\Api\ProductService as ApiProductService;
use FourPaws\MobileApiBundle\Services\Api\UserService as ApiUserService;
use FourPaws\PersonalBundle\Service\StampService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Response;
use FourPaws\MobileApiBundle\Dto\Response\PersonalBonusResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{
    /**
     * @var ApiUserService
     */
    private $apiUserService;
    /**
     * @var StampService
     */
    protected $stampService;
    /**
     * @var ApiProductService
     */
    private $apiProductService;

    public function __construct(ApiUserService $apiUserService, StampService $stampService, ApiProductService $apiProductService)
    {
        $this->apiUserService = $apiUserService;
        $this->stampService = $stampService;
        $this->apiProductService = $apiProductService;
    }

    /**
     * @Rest\Post(path="/user_login/", name="user_login")
     * @Rest\View()
     *
     * @Parameter(
     *     name="token",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="identifier token from /start/ request"
     * )
     * @Response(
     *     response="200"
     * )
     *
     * @param LoginRequest $loginRequest
     * @return ApiResponse
     *
     * @throws ArgumentException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\Helpers\Exception\WrongPhoneNumberException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
     * @throws \FourPaws\UserBundle\Exception\ExpiredConfirmCodeException
     * @throws \FourPaws\UserBundle\Exception\NotFoundConfirmedCodeException
     * @internal param Request $request
     */
    public function loginAction(LoginRequest $loginRequest): ApiResponse
    {
        return (new ApiResponse())
            ->setData($this->apiUserService->loginOrRegister($loginRequest));
    }

    /**
     * @Rest\Get(path="/logout/", name="logout")
     * @Rest\View()
     * @Response(
     *     response="200"
     * )
     * @Security("has_role('REGISTERED_USERS')", message="Вы не авторизованы")
     *
     * @throws \FourPaws\MobileApiBundle\Exception\RuntimeException
     */
    public function logoutAction(): ApiResponse
    {
        return (new ApiResponse())
            ->setData($this->apiUserService->logout());
    }

    /**
     * @Rest\Get(path="/user_info/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')", message="Вы не авторизованы")
     *
     * @return ApiResponse
     *
     * @throws ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
     */
    public function getUserInfoAction()
    {
        return (new ApiResponse())
            ->setData([
                'user' => $this->apiUserService->getCurrentApiUser(),
            ]);
    }

    /**
     * @Rest\Post(path="/user_info/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param PostUserInfoRequest $userInfoRequest
     * @return ApiResponse
     *
     * @throws ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
     */
    public function postUserInfoAction(PostUserInfoRequest $userInfoRequest): ApiResponse
    {
        return (new ApiResponse())
            ->setData($this->apiUserService->update($userInfoRequest->getUser()));
    }

    /**
     * @Rest\Get(path="/login_exist/")
     * @Rest\View()
     * @Security("!has_role('REGISTERED_USERS')", message="Вы уже авторизованы")
     * @Parameter(
     *     name="login",
     *     in="query",
     *     type="string",
     *     required=true,
     *     description="user phone"
     * )
     *
     * @param LoginExistRequest $loginExistRequest
     * @return ApiResponse
     *
     * @throws ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function loginExistAction(LoginExistRequest $loginExistRequest): ApiResponse
    {
        $doesLoginExist = $this->apiUserService->doesExist($loginExistRequest->getLogin());
        return (new ApiResponse())
            ->setData([
                'exist'         => $doesLoginExist,
                'feedback_text' => $doesLoginExist ? '' : 'Проверьте правильность заполнения поля. Введите ваш E-mail или номер телефона',
            ]);
    }

    /**
     * @Rest\Get("/personal_bonus/")
     * @Rest\View()
     *
     * @return PersonalBonusResponse
     * @throws ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException
     * @throws \FourPaws\External\Exception\ManzanaServiceException
     * @throws \FourPaws\UserBundle\Exception\EmptyPhoneException
     */
    public function getPersonalBonusAction(): PersonalBonusResponse
    {
        return (new PersonalBonusResponse())
            ->setPersonalBonus($this->apiUserService->getPersonalBonus());
    }

    /**
     * @Rest\Post(path="/email_code/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @param VerificationCodeSendByEmailRequest $verificationCodeRequest
     * @return ApiResponse
     */
    public function postEmailCodeAction(VerificationCodeSendByEmailRequest $verificationCodeRequest): ApiResponse
    {
        return (new ApiResponse())
            ->setData([
                'result' => CEvent::SendImmediate(
                    'VerificationCode',
                    's1',
                    [
                        'USER_EMAIL' => $verificationCodeRequest->getEmail(),
                        'CODE' => $verificationCodeRequest->getCode(),
                        'TEXT' => 'Код подтверждения смены адреса электронной почты. Если вы не вносили этих изменений, свяжитесь с нами по телефону +7 (800) 770-00-22',
                    ],
                    'N'
                )
            ]);
    }

    /**
     * @Rest\Get("/stamps/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @return ApiResponse
     * @throws ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getStampsInfoAction(): ApiResponse
    {
        try {
            $stamps = $this->stampService->getActiveStampsCount();
        } catch (\Exception $e) {
            $stamps = 0;
        }

        $exchangeRules = StampService::EXCHANGE_RULES;
        $productsXmlIds = array_keys($exchangeRules);

        $productsListCollection = $this->apiProductService->getListFromXmlIds($productsXmlIds, true);
        $productsList = $productsListCollection->get(0) ?? [];

        return (new ApiResponse())->setData([
            'stamps' => [
                'actionID' => false,
                'amount' => $stamps,
                'rate_val' => StampService::MARK_RATE,
                //IMPORTANT: В description переносы строк должны быть разделены с помощью \n\n
                'description' => 'Наступает осенняя пора, дети идут в школу, начинаются учебные будни. Вы можете вместе с питомцем тоже начать учиться',
                'second_description' => '1. Делай покупки, получай марки: 1 марка  = ' . StampService::MARK_RATE . ' руб.;'
                    . "\n\n2. Отслеживай марки где удобно: на чеке, в личном кабинете на сайте и в приложении;"
                    . "\n\n3. Выбери игру и добавь в корзину, нажми \"списать марки\";"
                    . "\n\n4. Получи игру со скидкой и развивай питомца!",
                'goods' => $productsList,
            ],
        ]);
    }

    /**
     * @Rest\Get("/stamps_october/")
     * @Rest\View()
     * @Security("has_role('REGISTERED_USERS')")
     *
     * @return ApiResponse
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws IblockNotFoundException
     */
    public function getStampsOctoberInfoAction(): ApiResponse
    {
        try {
            $stamps = $this->stampService->getActiveStampsCount();
        } catch (\Exception $e) {
            $stamps = 0;
        }

        if ($this->stampService->getNextDiscount() !== null) {
            $marksDeclension = new Declension('марку', 'марки', 'марок');
            $textNext = sprintf('До скидки -%s%% осталось %s %s', $this->stampService->getNextDiscount(), $this->stampService->getNextDiscountStampsNeed(), $marksDeclension->get($this->stampService->getNextDiscountStampsNeed()));
        } else {
            $textNext = 'Доступна максимальная скидка';
        }

        $actionCode = 'kopi-marki-pokupay-lezhaki-i-kogtetochki-so-skidkoy-30-';
        $actionIblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES);
        $arAction = \CIBlockElement::GetList(false, ['IBLOCK_ID' => $actionIblockId, '=CODE' => $actionCode], false, false, ['ID', 'IBLOCK_ID'])->GetNext();

        return (new ApiResponse())->setData([
            'stamps' => [
                'amount' => $stamps,
                'rate_val' => StampService::MARK_RATE,
                //IMPORTANT: В description переносы строк должны быть разделены с помощью \n\n
                'description' => '1. Делай любые покупки, копи марки: 1 марка  = ' . StampService::MARK_RATE . ' руб.;'
                    . "\n\n2. Отслеживай баланс марок: на чеке, в личном кабинете и в приложении;"
                    . "\n\n3. Покупай со скидкой до -30%;"
                    . "\n\n- на сайте и в приложении: добавь товар в корзину, нажми \"списать марки\";"
                    . "\n\n- в магазине: предъяви буклет или сообщи кассиру номер телефона;",
                'stampCategories' => $this->apiProductService->getStampsCategories(),
                'actionID' => ($arAction) ? $arAction['ID'] : 102862,
                'discount' => sprintf('%s%%', $this->stampService->getCurrentDiscount()),
                'textNext' => $textNext,
            ],
        ]);
    }
}
