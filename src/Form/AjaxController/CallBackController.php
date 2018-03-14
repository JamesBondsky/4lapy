<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Form\AjaxController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Callback\CallbackService;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\Form\FormService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CallBackController
 *
 * @package FourPaws\UserBundle\AjaxController
 */
class CallBackController extends Controller
{
    /** @var AjaxMess */
    private $ajaxMess;

    public function __construct(
        AjaxMess $ajaxMess
    ) {
        $this->ajaxMess = $ajaxMess;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addAction(Request $request): JsonResponse
    {
        try {
            $data = $request->request->all();
            $container = App::getInstance()->getContainer();

            /** @var FormService $formService */
            $formService = $container->get('form.service');

            $requiredFields = [
                'name',
                'phone',
                'time_call',
            ];
            $formatedFields = $formService->getRealNamesFields(
                (int)$data['WEB_FORM_ID']
            );
            if (!$formService->checkRequiredFields($data,
                array_intersect_key($formatedFields, array_flip($requiredFields)))) {
                return $this->ajaxMess->getEmptyDataError();
            }

            try {
                $data[$formatedFields['phone']] = PhoneHelper::normalizePhone($data[$formatedFields['phone']]);
            } catch (WrongPhoneNumberException $e) {
                return $this->ajaxMess->getWrongPhoneNumberException();
            }

            if ($formService->addResult($data)) {
                if (!empty($data['phone'])) {
                    /** @noinspection PhpUnhandledExceptionInspection */
                    Loader::includeModule('form');
                    $answer = new \CFormAnswer();
                    $arAnswer = $answer->GetByID($data[$formatedFields['time_call']])->Fetch();
                    $timeout = $arAnswer['FIELD_PARAM'] ?? 0;
                    /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
                    $date = new DateTime();
                    /** @noinspection PhpUnhandledExceptionInspection */
                    /** @var CallbackService $callbackService */
                    $callbackService = $container->get('callback.service');
                    $callbackService->send(
                        $data['phone'],
                        $date->format('Y-m-d H:i:s'),
                        $timeout
                    );
                }
                JsonSuccessResponse::create('Ваша завка принята');
            } else {
                return $this->ajaxMess->getUpdateError();
            }
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|ObjectException|LoaderException $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }
}
