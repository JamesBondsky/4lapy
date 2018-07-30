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
 * @package FourPaws\Form\AjaxController
 */
class CallBackController extends Controller
{
    /** @var AjaxMess */
    private $ajaxMess;

    public function __construct() {
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
            $formattedFields = $formService->getRealNamesFields(
                (int)$data['WEB_FORM_ID']
            );
            if (!$formService->checkRequiredFields($data,
                array_intersect_key($formattedFields, array_flip($requiredFields)))) {
                return $this->ajaxMess->getEmptyDataError();
            }

            try {
                /** добавляем 8 спереди */
                $data[$formattedFields['phone']] = PhoneHelper::formatPhone($data[$formattedFields['phone']], PhoneHelper::FORMAT_URL);
            } catch (WrongPhoneNumberException $e) {
                return $this->ajaxMess->getWrongPhoneNumberException();
            }

            if ($formService->addResult($data)) {
                if (!empty($data[$formattedFields['phone']])) {
                    $date = new DateTime();
                    /** @noinspection PhpUnhandledExceptionInspection */
                    /** @var CallbackService $callbackService */
                    $callbackService = $container->get('callback.service');
                    $callbackService->send(
                        $data[$formattedFields['phone']],
                        $date->format('Y-m-d H:i:s')
                    );
                }
                return JsonSuccessResponse::create('Ваша завка принята');
            }

            return $this->ajaxMess->getUpdateError();
        } catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException|ObjectException|LoaderException $e) {
            $logger = LoggerFactory::create('system');
            $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
        }

        return $this->ajaxMess->getSystemError();
    }
}
