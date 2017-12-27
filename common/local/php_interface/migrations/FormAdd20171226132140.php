<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Bitrix\Main\Loader;

class FormAdd20171226132140 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Настройка форм';
    
    public function up()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        Loader::includeModule('form');
        $form = [
            'SID'              => 'feedback',
            'NAME'             => 'Обратная связь',
            'BUTTON'           => 'Отправить',
            'C_SORT'           => '100',
            'DESCRIPTION'      => 'Мы открыты для обратной связи с покупателями, партнерами и соискателями! Оставьте свой отзыв о работе компании «Четыре лапы» в форме, приведенной ниже',
            'DESCRIPTION_TYPE' => 'text',
            'CREATE_EMAIL'     => 'Y',
            'STATUSES'         => [
                [
                    'TITLE'         => 'default',
                    'ACTIVE'        => 'Y',
                    'DEFAULT_VALUE' => 'Y',
                ],
            ],
            'QUESTIONS'        => [
                [
                    'SID'                 => 'name',
                    'ACTIVE'              => 'Y',
                    'TITLE'               => 'Имя',
                    'TITLE_TYPE'          => 'text',
                    'REQUIRED'            => 'Y',
                    'FILTER_TITLE'        => 'Имя',
                    'IN_RESULTS_TABLE'    => 'Y',
                    'IN_EXCEL_TABLE'      => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Имя',
                    'ANSWERS'             => [
                        [
                            'MESSAGE'    => 'Имя',
                            'FIELD_TYPE' => 'text',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '100',
                        ],
                    ],
                ],
                [
                    'SID'                 => 'email',
                    'ACTIVE'              => 'Y',
                    'TITLE'               => 'Эл. почта',
                    'TITLE_TYPE'          => 'text',
                    'REQUIRED'            => 'Y',
                    'FILTER_TITLE'        => 'Эл. почта',
                    'IN_RESULTS_TABLE'    => 'Y',
                    'IN_EXCEL_TABLE'      => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Эл. почта',
                    'ANSWERS'             => [
                        [
                            'MESSAGE'    => 'Эл. почта',
                            'FIELD_TYPE' => 'text',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '200',
                        ],
                    ],
                ],
                [
                    'SID'                 => 'phone',
                    'ACTIVE'              => 'Y',
                    'TITLE'               => 'Телефон',
                    'TITLE_TYPE'          => 'text',
                    'REQUIRED'            => 'Y',
                    'FILTER_TITLE'        => 'Телефон',
                    'IN_RESULTS_TABLE'    => 'Y',
                    'IN_EXCEL_TABLE'      => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Телефон',
                    'ANSWERS'             => [
                        [
                            'MESSAGE'    => 'Телефон',
                            'FIELD_TYPE' => 'text',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '300',
                        ],
                    ],
                ],
                [
                    'SID'                 => 'theme',
                    'ACTIVE'              => 'Y',
                    'TITLE'               => 'Тема',
                    'TITLE_TYPE'          => 'text',
                    'REQUIRED'            => 'Y',
                    'FILTER_TITLE'        => 'Тема',
                    'IN_RESULTS_TABLE'    => 'Y',
                    'IN_EXCEL_TABLE'      => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Тема',
                    'ANSWERS'             => [
                        [
                            'MESSAGE'    => 'Отзыв о работе интернет-магазина',
                            'FIELD_TYPE' => 'dropdown',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '100',
                        ],
                        [
                            'MESSAGE'    => 'Отзыв о работе магазина',
                            'FIELD_TYPE' => 'dropdown',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '200',
                        ],
                        [
                            'MESSAGE'    => 'Отзыв о товаре',
                            'FIELD_TYPE' => 'dropdown',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '300',
                        ],
                        [
                            'MESSAGE'    => 'Предложение в отдел закупок',
                            'FIELD_TYPE' => 'dropdown',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '400',
                        ],
                        [
                            'MESSAGE'    => 'Предложение по рекламе',
                            'FIELD_TYPE' => 'dropdown',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '500',
                        ],
                        [
                            'MESSAGE'    => 'Предложение по аренде',
                            'FIELD_TYPE' => 'dropdown',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '600',
                        ],
                        [
                            'MESSAGE'    => 'Другое',
                            'FIELD_TYPE' => 'dropdown',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '700',
                        ],
                    ],
                ],
                [
                    'SID'                 => 'message',
                    'ACTIVE'              => 'Y',
                    'TITLE'               => 'Сообщение',
                    'TITLE_TYPE'          => 'text',
                    'REQUIRED'            => 'Y',
                    'FILTER_TITLE'        => 'Сообщение',
                    'IN_RESULTS_TABLE'    => 'Y',
                    'IN_EXCEL_TABLE'      => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Сообщение',
                    'ANSWERS'             => [
                        [
                            'MESSAGE'    => 'Сообщение',
                            'FIELD_TYPE' => 'textarea',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '500',
                        ],
                    ],
                ],
                [
                    'SID'                 => 'file',
                    'ACTIVE'              => 'Y',
                    'TITLE'               => 'Файл',
                    'TITLE_TYPE'          => 'text',
                    'REQUIRED'            => 'N',
                    'FILTER_TITLE'        => 'Файл',
                    'IN_RESULTS_TABLE'    => 'Y',
                    'IN_EXCEL_TABLE'      => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Файл',
                    'ANSWERS'             => [
                        [
                            'MESSAGE'    => 'Имя',
                            'FIELD_TYPE' => 'file',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '600',
                        ],
                    ],
                ],
            ],
        ];
        
        $this->addForm($form);
        
        $form = [
            'SID'              => 'callback',
            'NAME'             => 'Обратный звонок',
            'BUTTON'           => 'Отправить',
            'C_SORT'           => '100',
            'DESCRIPTION'      => '<dl class="b-phone-pair">
        <dt class="b-phone-pair__phone b-phone-pair__phone--small-blue">Хотите поговорить?</dt>
        <dd class="b-phone-pair__description">Оставьте телефон, мы вам перезвоним</dd>
    </dl>',
            'DESCRIPTION_TYPE' => 'html',
            'CREATE_EMAIL'     => 'Y',
            'STATUSES'         => [
                [
                    'TITLE'         => 'default',
                    'ACTIVE'        => 'Y',
                    'DEFAULT_VALUE' => 'Y',
                ],
            ],
            'QUESTIONS'        => [
                [
                    'SID'                 => 'name',
                    'ACTIVE'              => 'Y',
                    'TITLE'               => 'Имя',
                    'TITLE_TYPE'          => 'text',
                    'REQUIRED'            => 'Y',
                    'FILTER_TITLE'        => 'Имя',
                    'IN_RESULTS_TABLE'    => 'Y',
                    'IN_EXCEL_TABLE'      => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Имя',
                    'ANSWERS'             => [
                        [
                            'MESSAGE'    => 'Имя',
                            'FIELD_TYPE' => 'text',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '100',
                        ],
                    ],
                ],
                [
                    'SID'                 => 'phone',
                    'ACTIVE'              => 'Y',
                    'TITLE'               => 'Телефон',
                    'TITLE_TYPE'          => 'text',
                    'REQUIRED'            => 'Y',
                    'FILTER_TITLE'        => 'Телефон',
                    'IN_RESULTS_TABLE'    => 'Y',
                    'IN_EXCEL_TABLE'      => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Телефон',
                    'ANSWERS'             => [
                        [
                            'MESSAGE'    => 'Телефон',
                            'FIELD_TYPE' => 'text',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '300',
                        ],
                    ],
                ],
                [
                    'SID'                 => 'time_call',
                    'ACTIVE'              => 'Y',
                    'TITLE'               => 'Время звонка',
                    'TITLE_TYPE'          => 'text',
                    'REQUIRED'            => 'Y',
                    'FILTER_TITLE'        => 'Время звонка',
                    'IN_RESULTS_TABLE'    => 'Y',
                    'IN_EXCEL_TABLE'      => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Время звонка',
                    'ANSWERS'             => [
                        [
                            'MESSAGE'    => 'Звонок сейчас',
                            'FIELD_TYPE' => 'dropdown',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '100',
                        ],
                        [
                            'MESSAGE'    => 'Звонок через 5 мин',
                            'FIELD_TYPE' => 'dropdown',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '200',
                        ],
                        [
                            'MESSAGE'    => 'Звонок через 15 мин',
                            'FIELD_TYPE' => 'dropdown',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '300',
                        ],
                        [
                            'MESSAGE'    => 'Звонок завтра',
                            'FIELD_TYPE' => 'dropdown',
                            'ACTIVE'     => 'Y',
                            'C_SORT'     => '400',
                        ],
                    ],
                ],
            ],
        ];
        
        $this->addForm($form);
        
        /** @noinspection PhpUnhandledExceptionInspection */
        //Option::set('form', 'SIMPLE', 'N');
    }
    
    /**
     * @param $form
     */
    private function addForm($form)
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
            $this->addStatuses($formId, $statuses);
            $this->addQuestions($formId, $questions);
            $this->addMailTemplate($formId, $createEmail);
        }
    }
    
    /**
     * @param int   $formId
     * @param array $statuses
     */
    private function addStatuses(int $formId, array $statuses = [])
    {
        if ($formId > 0 && \is_array($statuses) && !empty($statuses)) {
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
    private function addQuestions(int $formId, array $questions = [])
    {
        if ($formId > 0 && \is_array($questions) && !empty($questions)) {
            $obFormField = new \CFormField();
            foreach ($questions as $question) {
                $answers = [];
                if (isset($question['ANSWERS'])) {
                    $answers = $question['ANSWERS'];
                    unset($question['ANSWERS']);
                }
                $question['FORM_ID'] = $formId;
                $questionId          = (int)$obFormField->Set($question);
                $this->addAnswers($questionId, $answers);
            }
        }
    }
    
    /**
     * @param array $answers
     * @param int   $questionId
     */
    private function addAnswers(int $questionId, array $answers)
    {
        if ($questionId > 0 && \is_array($answers) && !empty($answers)) {
            $obFormAnswer = new \CFormAnswer();
            foreach ($answers as $answer) {
                $obFormAnswer->Set($answer);
            }
        }
    }
    
    /**
     * @param int    $formId
     * @param string $createEmail
     */
    private function addMailTemplate(int $formId, string $createEmail = 'N') : void
    {
        if ($createEmail === 'Y') {
            \CForm::SetMailTemplate($formId, 'Y');
        }
    }
    
    public function down()
    {
    }
}
