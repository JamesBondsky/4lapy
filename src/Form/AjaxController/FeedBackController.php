<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Form\AjaxController;

use FourPaws\App\Application as App;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\Form\Exception\FileSaveException;
use FourPaws\Form\Exception\FileSizeException;
use FourPaws\Form\Exception\FileTypeException;
use FourPaws\Form\FormService;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\ReCaptcha\ReCaptchaService;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FeedBackController
 *
 * @package FourPaws\UserBundle\AjaxController
 */
class FeedBackController extends Controller
{
    /**
     * @param Request $request
     *
     * @throws GuzzleException
     * @throws ServiceNotFoundException
     * @return JsonResponse
     */
    public function addAction(Request $request) : JsonResponse
    {
        $data = $request->request->all();
        
        try {
            /** @var FormService $formService */
            $formService = App::getInstance()->getContainer()->get('form.service');
            
            $requiredFields = [
                'name',
                'email',
                'phone',
                'theme',
                'message',
            ];
            $formatedFields = $formService->getRealNamesFields((int)$data['WEB_FORM_ID']);
            if (!$formService->checkRequiredFields($data, array_intersect_key($formatedFields, array_flip($requiredFields)))) {
                return JsonErrorResponse::createWithData(
                    'Не заполнены все обязательные поля',
                    ['errors' => ['emptyData' => 'Не заполнены все обязательные поля']]
                );
            }

            if (!$formService->validEmail($data[$formatedFields['email']])) {
                return JsonErrorResponse::createWithData(
                    'Некорректно заполнен эл. адрес',
                    ['errors' => ['wrongEmail' => 'Некорректно заполнен эл. адрес']]
                );
            }
            
            try {
                $data[$formatedFields['phone']] = PhoneHelper::normalizePhone($data[$formatedFields['phone']]);
            } catch (WrongPhoneNumberException $e) {
                return JsonErrorResponse::createWithData(
                    'Некорретно заполнен телефон',
                    ['errors' => ['wrongPhone' => 'Некорретно заполнен телефон']]
                );
            }
            
            $fileCode    = $formatedFields['file'];
            $fileSizeMb  = 2 * 1024 * 1024;
            $valid_types = [
                'jpg',
                'png',
                'doc',
                'docx',
            ];

            try {
                $file = $formService->saveFile($fileCode, $fileSizeMb, $valid_types);
                if (!empty($file)) {
                    $data[$fileCode] = $file;
                }
            } catch (FileSaveException $e) {
            } catch (FileSizeException $e) {
                return JsonErrorResponse::createWithData(
                    'Превышен максимально допустимый размер файла в 2Мб',
                    ['errors' => ['wrongPhone' => 'Превышен максимально допустимый размер файла в 2Мб']]
                );
            } catch (FileTypeException $e) {
                return JsonErrorResponse::createWithData(
                    'Неверный формат файла, допусимые форматы '.implode(', ', $valid_types),
                    ['errors' => ['wrongPhone' => 'Неверный формат файла, допусимые форматы '.implode(', ', $valid_types)]]
                );
            }
            
            if ($request->request->has('g-recaptcha-response')) {
                /** @var ReCaptchaService $recaptchaService */
                $recaptchaService = App::getInstance()->getContainer()->get('recaptcha.service');
                if (!$recaptchaService->checkCaptcha()) {
                    return JsonErrorResponse::createWithData(
                        'Проверка капчи не пройдена',
                        ['errors' => ['captchaError' => 'Проверка капчи не пройдена']]
                    );
                }
            }
            
            if ($formService->addResult($data)) {
                $_SESSION['FEEDBACK_SUCCESS'] = 'Y';
                
                return JsonSuccessResponse::create('Ваша завка принята', 200, [], ['reload' => true]);
            }
            
            return JsonErrorResponse::createWithData(
                'Произошла ошибка при сохранении',
                ['errors' => ['updateSave' => 'Произошла ошибка при сохранении']]
            );
        } catch (\Exception $e) {
        }
        
        return JsonErrorResponse::createWithData(
            'Неизвестаня ошибка. Пожалуйста обратитесь к администратору сайта',
            ['errors' => ['systemError' => 'Неизвестаня ошибка. Пожалуйста обратитесь к администратору сайта']]
        );
    }
}
