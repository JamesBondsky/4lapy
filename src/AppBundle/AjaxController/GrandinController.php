<?php

namespace FourPaws\AppBundle\AjaxController;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
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

        if (!$USER->IsAuthorized()) {
            return $this->ajaxMess->getNotAuthError();
        }

        $arFields = [$request->get('date'), $request->get('sum'), $request->get('surname'), $request->get('name'), $request->get('phone'), $request->get('email'), $request->get('rules')];
        if (count(array_filter($arFields)) < count($arFields)) {
            return $this->ajaxMess->getEmptyDataError();
        }

        //if (!ProtectorHelper::checkToken($request->get(ProtectorHelper::getField(ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD)), ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD)) {
        //    return $this->ajaxMess->getEmptyDataError();
        //}


        $iblockElement = new \CIBlockElement();
        $resultAdd = $iblockElement->Add([
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::GRANDIN_REQUEST),
            'NAME' => 'Заявка '.implode(' ', [$USER->GetID(), $request->get('surname'), $request->get('name')]),
            'PROPERTY_VALUES' => [
                'USER' => $USER->GetID(),
                'DATE' => $request->get('date'),
                'SUM' => $request->get('sum'),
                'SURNAME' => $request->get('surname'),
                'NAME' => $request->get('name'),
                'PHONE' => $request->get('phone'),
                'EMAIL' => $request->get('email'),
                'RULES' => $request->get('rules') == 'Y',
            ],
        ]);

        if (!$resultAdd) {
            return $this->ajaxMess->getAddError($iblockElement->LAST_ERROR);
        }

        $token = ProtectorHelper::generateToken(ProtectorHelper::TYPE_GRANDIN_REQUEST_ADD);
        return JsonSuccessResponse::createWithData('Спасибо за регистрацию', [
            'field' => $token['field'],
            'value' => $token['token'],
        ]);
    }

}
