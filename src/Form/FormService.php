<?php

namespace FourPaws\Form;

/**
 * Class FormService
 *
 * @package FourPaws\Form
 */
class FormService
{
    /**
     * @param array $fields
     * @param array $requireFields
     *
     * @return bool
     */
    public function checkRequiredFields(array $fields, array $requireFields = []) : bool
    {
        foreach ($requireFields as $requiredField) {
            if (empty($fields[$requiredField])) {
                return false;
                break;
            }
        }
        return true;
    }
    
    public function validEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }
        return true;
    }
    
    public function addResult($data)
    {
        if (isset($data['MAX_FILE_SIZE'])) {
            unset($data['MAX_FILE_SIZE']);
        }
        
        $webFormId = (int)$data['WEB_FORM_ID'];
        
        if (isset($data['g-recaptcha-response'])) {
            unset($data['g-recaptcha-response']);
        }
        global $USER;
        $userID = 0;
        if ($USER->IsAuthorized()) {
            $userID = (int)$USER->GetID();
        }
        unset($data['web_form_submit'], $data['WEB_FORM_ID']);
    
        $formResult = new \CFormResult();
        $res        = $formResult->Add($webFormId, $data, 'N', $userID > 0 ? $userID : false);
        return (int)$res > 0;
    }
    
    public function saveFile($fileCode, $fileSizeMb, $valid_types) : int{
        if (!empty($_FILES[$fileCode])) {
            $max_file_size = $fileSizeMb * 1024 * 1024;
        
            $file = $_FILES[$fileCode];
            if (is_uploaded_file($file['tmp_name'])) {
                $filename = $file['tmp_name'];
                $ext      = end(explode('.', $file['name']));
                if (filesize($filename) > $max_file_size) {
                    throw new FileSizeException('Файл не должен быть больше ' . $fileSizeMb . 'Мб');
                }
                if (!\in_array($ext, $valid_types, true)) {
                    throw new WrongPhoneNumberException('Разрешено загружать файлы только с расширениями ' . implode(' ,', $valid_types));
                }
            
                $fileId = (int)\CFile::SaveFile($file, 'form');
                if ($fileId > 0) {
                    return $fileId;
                }
    
                throw new WrongPhoneNumberException('Произошла ошибка при сохранении файла, попробуйте позже');
            }
    
            throw new WrongPhoneNumberException('Произошла ошибка при сохранении файла, попробуйте позже');
        }
        return 0;
    }
}