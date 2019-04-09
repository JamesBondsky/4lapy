<?php

namespace FourPaws\AppBundle\AjaxController;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Exception\JsonResponseException;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\ProtectorHelper;
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

    static $landingSites = ['s2', 's3'];

    static $grandinLanding = 'grandin';
    static $royalCaninLanding = 'royal_canin';

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

            if ($landingType == self::$grandinLanding) {
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

            if ($landingType == self::$grandinLanding && $request->get('sum') < 1800 || $landingType == self::$royalCaninLanding && $request->get('sum') < 1000) {
                throw new JsonResponseException($this->ajaxMess->getWrongDataError());
            }

            $email = $request->get('email');
            $userId = $USER->GetID();

            $requestIblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::GRANDIN_REQUEST);
            if (in_array($landingType, array_keys([self::$royalCaninLanding, self::$grandinLanding]))) {
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

}
