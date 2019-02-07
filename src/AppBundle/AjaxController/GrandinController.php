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
 * Class CommentsController
 *
 * @package FourPaws\AppBundle\AjaxController
 */
class GrandinController extends Controller
{

    static $petTypes = [
        'cat' => 'Кошка',
        'smallDog' => 'Собака мелкой породы',
        'otherDog' => 'Собака средней или крупной породы',
    ];

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

            $arFields = [$request->get('date'), $request->get('sum'), $request->get('surname'), $request->get('name'), $request->get('phone'), $request->get('email'), $request->get('rules'), $request->get('petType')];
            if (count(array_filter($arFields)) < count($arFields)) {
                throw new JsonResponseException($this->ajaxMess->getEmptyDataError());
            }

            if (!ProtectorHelper::checkToken($request->get(ProtectorHelper::getField(ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD)), ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD)) {
                throw new JsonResponseException($this->ajaxMess->getWrongParamsError());
            }

            if (!in_array($request->get('petType'), array_keys(self::$petTypes))) {
                throw new JsonResponseException($this->ajaxMess->getWrongDataError());
            }

            if ($request->get('sum') < 1800) {
                throw new JsonResponseException($this->ajaxMess->getWrongDataError());
            }

            $email = $request->get('email');

            $iblockElement = new \CIBlockElement();
            $resultAdd = $iblockElement->Add([
                'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::GRANDIN_REQUEST),
                'NAME' => 'Заявка ' . implode(' ', [$USER->GetID(), $request->get('surname'), $request->get('name')]),
                'PROPERTY_VALUES' => [
                    'USER' => $USER->GetID(),
                    'DATE' => $request->get('date'),
                    'SUM' => $request->get('sum'),
                    'SURNAME' => $request->get('surname'),
                    'NAME' => $request->get('name'),
                    'PHONE' => $request->get('phone'),
                    'EMAIL' => $email,
                    'RULES' => $request->get('rules') == 'Y',
                    'PET_TYPE' => self::$petTypes[$request->get('petType')],
                ],
            ]);

            if (!$resultAdd) {
                throw new JsonResponseException($this->ajaxMess->getAddError($iblockElement->LAST_ERROR));
            }

            try {
                $sender = App::getInstance()->getContainer()->get('expertsender.service');
                $sender->sendAfterCheckReg($email);
            }
            catch (\Exception $exception)
            {
                //FIXME залогировать
                /*$instance = static::getInstance();
                $instance->log()->critical(
                    sprintf(
                        '%s exception: %s',
                        __METHOD__,
                        $exception->getMessage()
                    )
                );*/
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
