<?php

namespace Sprint\Migration;

use FourPaws\FormBundle\Service\FormService;

class OrderFeedbackForm20181003161752 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "Миграция для создания формы обратной связи по заказу";

    public function up()
    {

        $formService = new FormService();

        $form = [
            'SID' => 'order_feedback',
            'NAME' => 'Обратная связь по заказу',
            'BUTTON' => 'Отправить',
            'C_SORT' => '100',
            'DESCRIPTION' => 'Оставьте свой отзыв по заказу №#ORDER_NUM#',
            'DESCRIPTION_TYPE' => 'text',
            'arSITE' => ['s1'],
            'USE_CAPTCHA' => 'N',
            'CREATE_EMAIL' => 'Y',
            'STATUSES' => [
                [
                    'TITLE' => 'default',
                    'ACTIVE' => 'Y',
                    'DEFAULT_VALUE' => 'Y',
                ],
            ],
            'QUESTIONS' => [
                [
                    'SID' => 'clientid',
                    'ACTIVE' => 'Y',
                    'TITLE' => 'ID клиента',
                    'TITLE_TYPE' => 'text',
                    'REQUIRED' => 'Y',
                    'FILTER_TITLE' => 'ID клиента',
                    'IN_RESULTS_TABLE' => 'Y',
                    'IN_EXCEL_TABLE' => 'Y',
                    'RESULTS_TABLE_TITLE' => 'ID клиента',
                    'ANSWERS' => [
                        [
                            'MESSAGE' => ' ',
                            'FIELD_TYPE' => 'hidden',
                            'ACTIVE' => 'Y',
                        ],
                    ],
                ],
                [
                    'SID' => 'order',
                    'ACTIVE' => 'Y',
                    'TITLE' => 'Номер заказа',
                    'TITLE_TYPE' => 'text',
                    'REQUIRED' => 'Y',
                    'FILTER_TITLE' => 'Номер заказа',
                    'IN_RESULTS_TABLE' => 'Y',
                    'IN_EXCEL_TABLE' => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Номер заказа',
                    'ANSWERS' => [
                        [
                            'MESSAGE' => ' ',
                            'FIELD_TYPE' => 'hidden',
                            'ACTIVE' => 'Y',
                        ],
                    ],
                ],
                [
                    'SID' => 'site_convenience',
                    'ACTIVE' => 'Y',
                    'TITLE' => 'Оцените удобство сайта',
                    'TITLE_TYPE' => 'text',
                    'REQUIRED' => 'N',
                    'FILTER_TITLE' => 'Удобство сайта',
                    'IN_RESULTS_TABLE' => 'Y',
                    'IN_EXCEL_TABLE' => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Удобство сайта',
                    'ANSWERS' => [
                        [
                            'MESSAGE' => ' ',
                            'FIELD_TYPE' => 'textarea',
                            'ACTIVE' => 'Y',
                        ],
                    ],
                ],
                $this->getRatingField('Удобство сайта', 'site_convenience'),
                [
                    'SID' => 'callcenter',
                    'ACTIVE' => 'Y',
                    'TITLE' => 'Оцените качество работы колл-центра',
                    'TITLE_TYPE' => 'text',
                    'REQUIRED' => 'N',
                    'FILTER_TITLE' => 'Колл-центр',
                    'IN_RESULTS_TABLE' => 'Y',
                    'IN_EXCEL_TABLE' => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Колл-центр',
                    'ANSWERS' => [
                        [
                            'MESSAGE' => ' ',
                            'FIELD_TYPE' => 'textarea',
                            'ACTIVE' => 'Y',
                        ],
                    ],
                ],
                $this->getRatingField('Колл-центр', 'callcenter'),
                [
                    'SID' => 'delivery',
                    'ACTIVE' => 'Y',
                    'TITLE' => 'Оцените качество работы службы доставки',
                    'TITLE_TYPE' => 'text',
                    'REQUIRED' => 'N',
                    'FILTER_TITLE' => 'Доставка',
                    'IN_RESULTS_TABLE' => 'Y',
                    'IN_EXCEL_TABLE' => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Доставка',
                    'ANSWERS' => [
                        [
                            'MESSAGE' => ' ',
                            'FIELD_TYPE' => 'textarea',
                            'ACTIVE' => 'Y',
                        ],
                    ],
                ],
                $this->getRatingField('Доставка', 'delivery'),
                [
                    'SID' => 'assortment',
                    'ACTIVE' => 'Y',
                    'TITLE' => 'Оцените представленный в магазине ассортимент',
                    'TITLE_TYPE' => 'text',
                    'REQUIRED' => 'N',
                    'FILTER_TITLE' => 'Ассортимент',
                    'IN_RESULTS_TABLE' => 'Y',
                    'IN_EXCEL_TABLE' => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Ассортимент',
                    'ANSWERS' => [
                        [
                            'MESSAGE' => ' ',
                            'FIELD_TYPE' => 'textarea',
                            'ACTIVE' => 'Y',
                        ],
                    ],
                ],
                $this->getRatingField('Ассортимент', 'assortment'),
                [
                    'SID' => 'impression',
                    'ACTIVE' => 'Y',
                    'TITLE' => 'Общее впечатление о компании',
                    'TITLE_TYPE' => 'text',
                    'REQUIRED' => 'N',
                    'FILTER_TITLE' => 'Общее впечатление',
                    'IN_RESULTS_TABLE' => 'Y',
                    'IN_EXCEL_TABLE' => 'Y',
                    'RESULTS_TABLE_TITLE' => 'Общее впечатление',
                    'ANSWERS' => [
                        [
                            'MESSAGE' => ' ',
                            'FIELD_TYPE' => 'textarea',
                            'ACTIVE' => 'Y',
                        ],
                    ],
                ],
                $this->getRatingField('Впечатления', 'impression'),
            ],
        ];

        $formService->addForm($form);

        $helper = new HelperManager();
        $eventName = 'FORM_FILLING_order_feedback';
        $eventHelper = $helper->Event();
        $eventHelper->updateEventMessageByFilter(['EVENT_NAME' => $eventName], ['EMAIL_TO' => 'welcome@4lapy.ru']);
    }

    public function down()
    {
        $formService = new FormService();
        $formService->deleteForm('order_feedback');
    }


    /**
     * @param string $fieldName
     *
     * @return array
     */
    private function getRatingField(string $fieldName, string $fieldCode): array
    {
        $sid                          = $fieldCode . '_rate';
        $fieldTitle                   = $fieldName . ' - оценка';
        $field                        = $this->exampleRatingField;
        $field['SID']                 = $sid;
        $field['TITLE']               = $fieldTitle;
        $field['FILTER_TITLE']        = $fieldTitle;
        $field['RESULTS_TABLE_TITLE'] = $fieldTitle;
        return $field;
    }

    private $exampleRatingField = [
        'SID' => '',
        'ACTIVE' => 'Y',
        'TITLE' => '',
        'TITLE_TYPE' => 'text',
        'REQUIRED' => 'Y',
        'FILTER_TITLE' => '',
        'IN_RESULTS_TABLE' => 'Y',
        'IN_EXCEL_TABLE' => 'Y',
        'RESULTS_TABLE_TITLE' => '',
        'ANSWERS' => [
            [
                'MESSAGE' => ' ',
                'FIELD_TYPE' => 'radio',
                'VALUE' => 1,
                'FIELD_PARAM' => 1,
                'C_SORT' => 100,
            ],
            [
                'MESSAGE' => ' ',
                'FIELD_TYPE' => 'radio',
                'VALUE' => 2,
                'FIELD_PARAM' => 2,
                'C_SORT' => 200,
            ],
            [
                'MESSAGE' => ' ',
                'FIELD_TYPE' => 'radio',
                'VALUE' => 3,
                'FIELD_PARAM' => 3,
                'C_SORT' => 300,
            ],
            [
                'MESSAGE' => ' ',
                'FIELD_TYPE' => 'radio',
                'VALUE' => 4,
                'FIELD_PARAM' => 4,
                'C_SORT' => 400,
            ],
            [
                'MESSAGE' => ' ',
                'FIELD_TYPE' => 'radio',
                'VALUE' => 5,
                'FIELD_PARAM' => 5,
                'C_SORT' => 500,
            ],
        ],
    ];
}
