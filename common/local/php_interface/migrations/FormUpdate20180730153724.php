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
class FormUpdate20180730153724 extends SprintMigrationBase
{
    protected $description = 'Обновление формы обратного звонка';

    /**
     * @return bool|void
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function up()
    {
        $formService = App::getInstance()->getContainer()->get('form.service');

        $formService->deleteForm('callback');

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
                ]
            ],
        ];

        $formService->addForm($form);

        /** @noinspection PhpUnhandledExceptionInspection */
        Option::set('form', 'SIMPLE', 'N');
    }

    public function down()
    {

    }
}
