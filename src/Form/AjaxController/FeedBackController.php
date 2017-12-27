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
 * Class FeedBackController
 *
 * @package FourPaws\UserBundle\AjaxController
 */
class FeedBackController extends Controller
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
            'phone',
            'theme',
            'message',
        ];
        if(!$formService->checkRequiredFields($data, $requiredFields)){
            return JsonErrorResponse::create('Не заполнены все обязательные поля');
        }
        
        if (!$formService->validEmail($data['email'])) {
            return JsonErrorResponse::create('Некорректно заполнен эл. адрес');
        }
        
        try {
            $data['phone'] = PhoneHelper::normalizePhone($data['phone']);
        } catch (WrongPhoneNumberException $e) {
            return JsonErrorResponse::create('Некорретно заполнен телефон');
        }
        
        $fileCode = 'file';
        $fileSizeMb    = 2 * 1024 * 1024;
        $valid_types   = [
            'jpg',
            'png',
            'doc',
            'docx',
        ];
        if (!empty($_FILES[$fileCode])) {
            $max_file_size = $fileSizeMb * 1024 * 1024;
            
            $file = $_FILES[$fileCode];
            if (is_uploaded_file($file['tmp_name'])) {
                $filename = $file['tmp_name'];
                $ext      = end(explode('.', $file['name']));
                if (filesize($filename) > $max_file_size) {
                    return JsonErrorResponse::create('Файл не должен быть больше ' . $fileSizeMb . 'Мб');
                }
                if (!\in_array($ext, $valid_types, true)) {
                    return JsonErrorResponse::create(
                        'Разрешено загружать файлы только с расширениями ' . implode(' ,', $valid_types)
                    );
                }
                
                $fileId = (int)\CFile::SaveFile($file, 'form');
                if ($fileId > 0) {
                    $data[$fileCode] = $fileId;
                } else {
                    return JsonErrorResponse::create('Произошла ошибка при сохранении файла, попробуйте позже');
                }
            } else {
                return JsonErrorResponse::create('Произошла ошибка при сохранении файла, попробуйте позже');
            }
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
