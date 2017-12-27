<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Form\AjaxController;

use FourPaws\App\Application as App;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
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
        if(!$formService->checkRequiredFields($data, $requiredFields)){
            return JsonErrorResponse::create('Не заполнены все обязательные поля');
        }
        
        try {
            $data['phone'] = PhoneHelper::normalizePhone($data['phone']);
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::create('Некорретно заполнен телефон');
        }
        
        if (!App::getInstance()->getContainer()->get('recaptcha.service')->checkCaptcha()) {
            return JsonErrorResponse::create('Проверка капчи не пройдена');
        }
        
        if ($formService->addResult($data)) {
            JsonSuccessResponse::create('Ваша завка принята');
        } else {
            return JsonErrorResponse::create('Произошла ошибка при сохранении');
        }
        
        return JsonErrorResponse::create('Неизвестаня ошибка');
    }
}
