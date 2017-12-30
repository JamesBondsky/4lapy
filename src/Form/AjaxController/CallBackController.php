<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Form\AjaxController;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
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
    /**
     * @param Request $request
     *
     * @throws \RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectException
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return JsonResponse
     */
    public function addAction(Request $request) : JsonResponse
    {
        $data = $request->request->getIterator()->getArrayCopy();
        
        $formService = App::getInstance()->getContainer()->get('form.service');
        
        $requiredFields = [
            'name',
            'email',
            'time_call',
        ];
        if (!$formService->checkRequiredFields($data, $requiredFields)) {
            return JsonErrorResponse::createWithData(
                'Не заполнены все обязательные поля',
                ['errors' => ['emptyData' => 'Не заполнены все обязательные поля']]
            );
        }
        
        try {
            $data['phone'] = PhoneHelper::normalizePhone($data['phone']);
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::createWithData(
                'Некорретно заполнен телефон',
                ['errors' => ['wrongPhone' => 'Некорретно заполнен телефон']]
            );
        }
        
        if (!App::getInstance()->getContainer()->get('recaptcha.service')->checkCaptcha()) {
            return JsonErrorResponse::createWithData(
                'Проверка капчи не пройдена',
                ['errors' => ['captchaError' => 'Проверка капчи не пройдена']]
            );
        }
        
        if ($formService->addResult($data)) {
            if(!empty($data['phone'])) {
                /** @noinspection PhpUnhandledExceptionInspection */
                Loader::includeModule('form');
                $answer   = new \CFormAnswer();
                $arAnswer = $answer->GetByID($data['time_call'])->Fetch();
                $timeout  = $arAnswer['FIELD_PARAM'] ?? 0;
                $date     = new DateTime();
                /** @noinspection PhpUnhandledExceptionInspection */
                App::getInstance()->getContainer()->get('callback.service')->send(
                    $data['phone'],
                    $date->format('Y-m-d H:i:s'),
                    $timeout
                );
            }
            JsonSuccessResponse::create('Ваша завка принята');
        } else {
            return JsonErrorResponse::createWithData(
                'Произошла ошибка при сохранении',
                ['errors' => ['errorSave' => 'Произошла ошибка при сохранении']]
            );
        }
        
        return JsonErrorResponse::createWithData(
            'Неизвестаня ошибка. Пожалуйста обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Неизвестаня ошибка. Пожалуйста обратитесь к администратору сайта']]
        );
    }
}
