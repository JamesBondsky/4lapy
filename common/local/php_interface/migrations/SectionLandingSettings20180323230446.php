<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Sprint\Migration\Helpers\UserTypeEntityHelper;

class SectionLandingSettings20180323230446 extends SprintMigrationBase
{

    public const ENTITY_ID = 'IBLOCK_2_SECTION';
    protected $description = 'Настройка лендинга на разделе';

    /**
     * @return bool
     * @throws IblockNotFoundException
     * @throws ApplicationCreateException
     */
    public function up():bool
    {
        /** @var UserTypeEntityHelper $userTypeEntityHelper */

        $field = 'UF_LANDING';
        if ($this->getHelper()->UserTypeEntity()->addUserTypeEntityIfNotExists(static::ENTITY_ID, 'UF_LANDING', [
            'ENTITY_ID'         => static::ENTITY_ID,
            'FIELD_NAME'        => $field,
            'USER_TYPE_ID'      => 'boolean',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'DEFAULT_VALUE'  => 0,
                    'DISPLAY'        => 'CHECKBOX',
                    'LABEL'          =>
                        [
                            0 => '',
                            1 => '',
                        ],
                    'LABEL_CHECKBOX' => '',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Является лендингом',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Является лендингом',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Является лендингом',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Является лендингом',
                ],
        ])) {
            $this->log()->info('Пользовательское свойство ' . $field . ' создано');
        } else {
            $this->log()->error('Ошибка при создании пользовательского свойства ' . $field);
            return false;
        }

        $field = 'UF_LANDING_BANNER';
        if ($this->getHelper()->UserTypeEntity()->addUserTypeEntityIfNotExists(static::ENTITY_ID, 'UF_LANDING_BANNER', [
            'ENTITY_ID'         => static::ENTITY_ID,
            'FIELD_NAME'        => $field,
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => '',
            'SORT'              => '100',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'N',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'SIZE'          => 100,
                    'ROWS'          => 8,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Баннер для лендинга',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Баннер для лендинга',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Баннер для лендинга',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Баннер для лендинга',
                ],
        ])) {
            $this->log()->info('Пользовательское свойство ' . $field . ' создано');
        } else {
            $this->log()->error('Ошибка при создании пользовательского свойства ' . $field);
            return false;
        }

        $this->log()->info('создание ве-формы faq');
        $formService = Application::getInstance()->getContainer()->get('form.service');

        $form = [
            'SID'              => 'faq',
            'NAME'             => 'Вопросы и ответы',
            'BUTTON'           => 'Отправить',
            'C_SORT'           => '100',
            'DESCRIPTION'      => '',
            'DESCRIPTION_TYPE' => 'text',
            'arSITE'           => ['s1'],
            'USE_CAPTCHA'      => 'Y',
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
                        ],
                    ],
                ],
            ],
        ];

        $formService->addForm($form);
        $this->log()->info('Веб форма faq создана');

        $this->log()->info('установка свойства привязки к разделу для баннеров');
        $this->getHelper()->Iblock()->addPropertyIfNotExists(IblockUtils::getIblockId(IblockType::PUBLICATION,
            IblockCode::BANNERS), [
            'NAME'               => 'Привязка к разделу',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'CODE'               => 'SECTION',
            'DEFAULT_VALUE'      => '',
            'PROPERTY_TYPE'      => 'G',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '',
            'FILE_TYPE'          => '',
            'MULTIPLE_CNT'       => '5',
            'TMP_ID'             => null,
            'LINK_IBLOCK_ID'     => '2',
            'WITH_DESCRIPTION'   => 'N',
            'SEARCHABLE'         => 'N',
            'FILTRABLE'          => 'N',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '2',
            'USER_TYPE'          => null,
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
        ]);
        $this->log()->info('свойство привязки к разделу для баннеров установлено');

        $this->log()->info('Создание инфоблока faq');
        $this->getHelper()->Iblock()->addIblockTypeIfNotExists([
            'ID'               => 'publications',
            'SECTIONS'         => 'Y',
            'EDIT_FILE_BEFORE' => '',
            'EDIT_FILE_AFTER'  => '',
            'IN_RSS'           => 'N',
            'SORT'             => '200',
            'LANG'             =>
                [
                    'ru' =>
                        [
                            'NAME'         => 'Публикации',
                            'SECTION_NAME' => '',
                            'ELEMENT_NAME' => 'Публикация',
                        ],
                ],
        ]);

        $iblockId = $this->getHelper()->Iblock()->addIblockIfNotExists([
            'IBLOCK_TYPE_ID'     => 'publications',
            'LID'                => 's1',
            'CODE'               => 'faq',
            'NAME'               => 'Часто задаваемые вопросы',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'LIST_PAGE_URL'      => '',
            'DETAIL_PAGE_URL'    => '',
            'SECTION_PAGE_URL'   => '',
            'CANONICAL_PAGE_URL' => '',
            'PICTURE'            => null,
            'DESCRIPTION'        => '',
            'DESCRIPTION_TYPE'   => 'text',
            'RSS_TTL'            => '24',
            'RSS_ACTIVE'         => 'Y',
            'RSS_FILE_ACTIVE'    => 'N',
            'RSS_FILE_LIMIT'     => null,
            'RSS_FILE_DAYS'      => null,
            'RSS_YANDEX_ACTIVE'  => 'N',
            'XML_ID'             => '',
            'TMP_ID'             => 'adff9b782fbee110affad384321ce2e3',
            'INDEX_ELEMENT'      => 'N',
            'INDEX_SECTION'      => 'N',
            'WORKFLOW'           => 'N',
            'BIZPROC'            => 'N',
            'SECTION_CHOOSER'    => 'L',
            'LIST_MODE'          => '',
            'RIGHTS_MODE'        => 'S',
            'SECTION_PROPERTY'   => 'N',
            'PROPERTY_INDEX'     => 'N',
            'VERSION'            => '2',
            'LAST_CONV_ELEMENT'  => '0',
            'SOCNET_GROUP_ID'    => null,
            'EDIT_FILE_BEFORE'   => '',
            'EDIT_FILE_AFTER'    => '',
            'SECTIONS_NAME'      => 'Категории',
            'SECTION_NAME'       => 'Категория',
            'ELEMENTS_NAME'      => 'Вопросы',
            'ELEMENT_NAME'       => 'Вопрос',
            'EXTERNAL_ID'        => '',
            'LANG_DIR'           => '/',
            'SERVER_NAME'        => 'stage.4lapy.adv.ru',
        ]);

        $this->getHelper()->Iblock()->updateIblockFields($iblockId, [
            'IBLOCK_SECTION'    =>
                [
                    'NAME'          => 'Привязка к разделам',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' =>
                        [
                            'KEEP_IBLOCK_SECTION_ID' => 'N',
                        ],
                ],
            'ACTIVE'            =>
                [
                    'NAME'          => 'Активность',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => 'Y',
                ],
            'ACTIVE_FROM'       =>
                [
                    'NAME'          => 'Начало активности',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
            'ACTIVE_TO'         =>
                [
                    'NAME'          => 'Окончание активности',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
            'SORT'              =>
                [
                    'NAME'          => 'Сортировка',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '0',
                ],
            'NAME'              =>
                [
                    'NAME'          => 'Название',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => '',
                ],
            'PREVIEW_PICTURE'   =>
                [
                    'NAME'          => 'Картинка для анонса',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' =>
                        [
                            'FROM_DETAIL'             => 'N',
                            'SCALE'                   => 'N',
                            'WIDTH'                   => '',
                            'HEIGHT'                  => '',
                            'IGNORE_ERRORS'           => 'N',
                            'METHOD'                  => 'resample',
                            'COMPRESSION'             => 95,
                            'DELETE_WITH_DETAIL'      => 'N',
                            'UPDATE_WITH_DETAIL'      => 'N',
                            'USE_WATERMARK_TEXT'      => 'N',
                            'WATERMARK_TEXT'          => '',
                            'WATERMARK_TEXT_FONT'     => '',
                            'WATERMARK_TEXT_COLOR'    => '',
                            'WATERMARK_TEXT_SIZE'     => '',
                            'WATERMARK_TEXT_POSITION' => 'tl',
                            'USE_WATERMARK_FILE'      => 'N',
                            'WATERMARK_FILE'          => '',
                            'WATERMARK_FILE_ALPHA'    => '',
                            'WATERMARK_FILE_POSITION' => 'tl',
                            'WATERMARK_FILE_ORDER'    => null,
                        ],
                ],
            'PREVIEW_TEXT_TYPE' =>
                [
                    'NAME'          => 'Тип описания для анонса',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => 'text',
                ],
            'PREVIEW_TEXT'      =>
                [
                    'NAME'          => 'Описание для анонса',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
            'DETAIL_PICTURE'    =>
                [
                    'NAME'          => 'Детальная картинка',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' =>
                        [
                            'SCALE'                   => 'N',
                            'WIDTH'                   => '',
                            'HEIGHT'                  => '',
                            'IGNORE_ERRORS'           => 'N',
                            'METHOD'                  => 'resample',
                            'COMPRESSION'             => 95,
                            'USE_WATERMARK_TEXT'      => 'N',
                            'WATERMARK_TEXT'          => '',
                            'WATERMARK_TEXT_FONT'     => '',
                            'WATERMARK_TEXT_COLOR'    => '',
                            'WATERMARK_TEXT_SIZE'     => '',
                            'WATERMARK_TEXT_POSITION' => 'tl',
                            'USE_WATERMARK_FILE'      => 'N',
                            'WATERMARK_FILE'          => '',
                            'WATERMARK_FILE_ALPHA'    => '',
                            'WATERMARK_FILE_POSITION' => 'tl',
                            'WATERMARK_FILE_ORDER'    => null,
                        ],
                ],
            'DETAIL_TEXT_TYPE'  =>
                [
                    'NAME'          => 'Тип детального описания',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => 'text',
                ],
            'DETAIL_TEXT'       =>
                [
                    'NAME'          => 'Детальное описание',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
            'XML_ID'            =>
                [
                    'NAME'          => 'Внешний код',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => '',
                ],
            'CODE'              =>
                [
                    'NAME'          => 'Символьный код',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' =>
                        [
                            'UNIQUE'          => 'N',
                            'TRANSLITERATION' => 'Y',
                            'TRANS_LEN'       => 100,
                            'TRANS_CASE'      => 'L',
                            'TRANS_SPACE'     => '-',
                            'TRANS_OTHER'     => '-',
                            'TRANS_EAT'       => 'Y',
                            'USE_GOOGLE'      => 'N',
                        ],
                ],
            'TAGS'              =>
                [
                    'NAME'          => 'Теги',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
        ]);
        $this->log()->info('Инфоблок faq создан');

        $this->log()->info('Создание структуры');
        $rootSect = $this->getHelper()->Iblock()->addSectionIfNotExists($iblockId, [
            'ACTIVE'            => 'Y',
            'NAME'              => 'Защита и лечение питомцев от блох и клещей',
            'CODE'              => 'fleas',
            'SORT'              => 10,
        ]);
        $this->getHelper()->Iblock()->addSectionIfNotExists($iblockId, [
            'ACTIVE'            => 'Y',
            'IBLOCK_SECTION_ID' => $rootSect,
            'NAME'              => 'Часто задаваемые вопросы',
            'CODE'              => 'faq',
            'SORT'              => 10,
        ]);
        $this->getHelper()->Iblock()->addSectionIfNotExists($iblockId, [
            'ACTIVE'            => 'Y',
            'IBLOCK_SECTION_ID' => $rootSect,
            'NAME'              => 'Вопросы пользователей',
            'CODE'              => 'users-questions',
            'SORT'              => 20,
        ]);
        $this->log()->info('Структура создана');

        return true;

    }
}
