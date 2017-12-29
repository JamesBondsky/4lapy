<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use FourPaws\App\Application as App;

/**
 * Class FormAdd20171226132140
 *
 * @package Sprint\Migration
 */
class FormAdd20171226132140 extends SprintMigrationBase
{
    protected $description = 'Настройка форм';
    
    /**
     * @return bool|void
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function up()
    {
        //Loader::includeModule('form');
    
        $formService = App::getInstance()->getContainer()->get('form.service');
        
        $form = [
            'SID'              => 'feedback',
            'NAME'             => 'Обратная связь',
            'BUTTON'           => 'Отправить',
            'C_SORT'           => '100',
            'DESCRIPTION'      => 'Мы открыты для обратной связи с покупателями, партнерами и соискателями! Оставьте свой отзыв о работе компании «Четыре лапы» в форме, приведенной ниже',
            'DESCRIPTION_TYPE' => 'text',
            'arSITE'=>['s1'],
            'USE_CAPTCHA' => 'Y',
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
                            'ACTIVE'     => 'Y'
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
                            'ACTIVE'     => 'Y'
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
                            'ACTIVE'     => 'Y'
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
                            'ACTIVE'     => 'Y'
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
                            'MESSAGE'    => 'Файл',
                            'FIELD_TYPE' => 'file',
                            'ACTIVE'     => 'Y'
                        ],
                    ],
                ],
            ],
        ];
    
        $formService->addForm($form);
        
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
            'arSITE'=>['s1'],
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
                            'ACTIVE'     => 'Y'
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
                            'ACTIVE'     => 'Y'
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
                            'FIELD_PARAM'     => 0,
                            'C_SORT'     => '100',
                        ],
                        [
                            'MESSAGE'    => 'Звонок через 5 мин',
                            'FIELD_TYPE' => 'dropdown',
                            'ACTIVE'     => 'Y',
                            'FIELD_PARAM'     => 5,
                            'C_SORT'     => '200',
                        ],
                        [
                            'MESSAGE'    => 'Звонок через 15 мин',
                            'FIELD_TYPE' => 'dropdown',
                            'ACTIVE'     => 'Y',
                            'FIELD_PARAM'     => 15,
                            'C_SORT'     => '300',
                        ],
                        [
                            'MESSAGE'    => 'Звонок завтра',
                            'FIELD_TYPE' => 'dropdown',
                            'ACTIVE'     => 'Y',
                            'FIELD_PARAM'     => 1440,
                            'C_SORT'     => '400',
                        ],
                    ],
                ],
            ],
        ];
    
        $formService->addForm($form);
        
        /** @noinspection PhpUnhandledExceptionInspection */
        Option::set('form', 'SIMPLE', 'N');
    }
    
    /**
     * @return bool|void
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function down()
    {
        //Loader::includeModule('form');
    
        $formService = App::getInstance()->getContainer()->get('form.service');
    
        $formService ->deleteForm('feedback');
        $formService ->deleteForm('callback');
    }
}
