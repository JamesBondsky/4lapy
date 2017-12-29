<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Form;

use FourPaws\Form\Exception\FileSaveException;
use FourPaws\Form\Exception\FileSizeException;
use FourPaws\Form\Exception\FileTypeException;

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
    
    /**
     * @param $email
     *
     * @return bool
     */
    public function validEmail($email) : bool
    {
        return !(filter_var($email, FILTER_VALIDATE_EMAIL) === false);
    }
    
    /**
     * @param $data
     *
     * @return bool
     */
    public function addResult($data) : bool
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
    
    /**
     * @param $fileCode
     * @param $fileSizeMb
     * @param $valid_types
     *
     * @throws \FourPaws\Form\Exception\FileSaveException
     * @throws \FourPaws\Form\Exception\FileSizeException
     * @throws \FourPaws\Form\Exception\FileTypeException
     * @return int
     */
    public function saveFile($fileCode, $fileSizeMb, $valid_types) : int
    {
        if (!empty($_FILES[$fileCode])) {
            $max_file_size = $fileSizeMb * 1024 * 1024;
            
            $file = $_FILES[$fileCode];
            if (is_uploaded_file($file['tmp_name'])) {
                $filename = $file['tmp_name'];
                /** @noinspection PassingByReferenceCorrectnessInspection */
                $ext = end(explode('.', $file['name']));
                if (filesize($filename) > $max_file_size) {
                    throw new FileSizeException('Файл не должен быть больше ' . $fileSizeMb . 'Мб');
                }
                if (!\in_array($ext, $valid_types, true)) {
                    throw new FileTypeException(
                        'Разрешено загружать файлы только с расширениями ' . implode(' ,', $valid_types)
                    );
                }
                
                $fileId = (int)\CFile::SaveFile($file, 'form');
                if ($fileId > 0) {
                    return $fileId;
                }
            }
            
            throw new FileSaveException('Произошла ошибка при сохранении файла, попробуйте позже');
        }
        
        return 0;
    }
    
    /**
     * @param $form
     */
    public function addForm($form)
    {
        $questions = [];
        if (isset($form['QUESTIONS'])) {
            $questions = $form['QUESTIONS'];
            unset($form['QUESTIONS']);
        }
        $createEmail = 'N';
        if (isset($form['CREATE_EMAIL'])) {
            $createEmail = $form['CREATE_EMAIL'];
            unset($form['CREATE_EMAIL']);
        }
        $statuses = [];
        if (isset($form['STATUSES'])) {
            $statuses = $form['STATUSES'];
            unset($form['STATUSES']);
        }
        $formId = (int)\CForm::Set($form);
        
        if ($formId > 0) {
            if (!empty($statuses)) {
                $this->addStatuses($formId, $statuses);
            }
            if (!empty($questions)) {
                $this->addQuestions($formId, $questions);
            }
            if ($createEmail === 'Y') {
                $this->addMailTemplate($formId, $createEmail);
            }
        }
    }
    
    /**
     * @param int   $formId
     * @param array $statuses
     */
    public function addStatuses(int $formId, array $statuses)
    {
        if ($formId > 0 && !empty($statuses)) {
            $obFormStatus = new \CFormStatus();
            foreach ($statuses as $status) {
                $status['FORM_ID'] = $formId;
                $obFormStatus->Set($status);
            }
        }
    }
    
    /**
     * @param int   $formId
     * @param array $questions
     */
    public function addQuestions(int $formId, array $questions)
    {
        if ($formId > 0 && !empty($questions)) {
            $obFormField = new \CFormField();
            foreach ($questions as $question) {
                $answers = [];
                if (isset($question['ANSWERS'])) {
                    $answers = $question['ANSWERS'];
                    unset($question['ANSWERS']);
                }
                $question['FORM_ID'] = $formId;
                $questionId          = (int)$obFormField->Set($question);
                if ($questionId > 0 && !empty($answers)) {
                    $this->addAnswers($questionId, $answers);
                }
            }
        }
    }
    
    /**
     * @param array $answers
     * @param int   $questionId
     */
    public function addAnswers(int $questionId, array $answers)
    {
        if ($questionId > 0 && !empty($answers)) {
            $obFormAnswer = new \CFormAnswer();
            foreach ($answers as $answer) {
                $answer['FIELD_ID'] = $questionId;
                $obFormAnswer->Set($answer);
            }
        }
    }
    
    /**
     * @param int    $formId
     * @param string $createEmail
     */
    public function addMailTemplate(int $formId, string $createEmail = 'N')
    {
        if ($createEmail === 'Y') {
            $arTemplates = \CForm::SetMailTemplate($formId, 'Y');
            \CForm::Set(['arMAIL_TEMPLATE' => $arTemplates], $formId);
        }
    }
    
    public function deleteForm($sid)
    {
        $by    = 'ID';
        $order = 'ASC';
        $res   = \CForm::GetList($by, $order, ['SID' => $sid]);
        while ($item = $res->Fetch()) {
            \CForm::Delete($item['ID']);
        }
    }
}
