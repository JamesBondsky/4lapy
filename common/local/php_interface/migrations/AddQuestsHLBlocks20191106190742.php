<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class AddQuestsHLBlocks20191106190742 extends SprintMigrationBase
{
    protected $description = 'Добавляет HL блоки для квеста';

    protected const PET_HL_TYPE = 'QuestPet';
    protected const PRIZE_HL_TYPE = 'QuestPrize';
    protected const USER_RESULT_HL_TYPE = 'QuestResult';
    protected const TASK_TASK_HL_TYPE = 'QuestTask';
    protected const PROMOCODE_HL_TYPE = 'QuestPromocode';

    protected const PET_HL_TABLE = '4lapy_quest_pet';
    protected const PRIZE_HL_TABLE = '4lapy_quest_prize';
    protected const USER_RESULT_HL_TABLE = '4lapy_quest_user_result';
    protected const TASK_HL_TABLE = '4lapy_quest_task';
    protected const PROMOCODE_HL_TABLE = '4lapy_quest_promocode';

    protected const PET_HL_NAME = 'Квест: питомцы';
    protected const PRIZE_HL_NAME = 'Квест: призы';
    protected const USER_RESULT_HL_NAME = 'Квест: результаты пользователей';
    protected const TASK_HL_NAME = 'Квест: задания';
    protected const PROMOCODE_HL_NAME = 'Квест: промокоды';

    protected $prizeHlBlockId;
    protected $prizeHLBlockFieldID;

    protected $petHLBlockId;
    protected $petHLBlockFieldID;

    protected $promocodeHlBlockId;

    protected $taskHlBlockId;

    protected $userResultHlBlockId;

    /**
     * @return bool|void
     * @throws IblockNotFoundException
     */
    public function up()
    {
        $this->addPrizeHLBlock();

        $this->addPetHLBlock();

        $this->addPromocodeHLBlock();

        $this->addTaskHlBlock();

        $this->addUserResultHLBlock();
    }

    public function down()
    {
        $helper = new HelperManager();

        $helper->Hlblock()->deleteHlblockIfExists(self::USER_RESULT_HL_TYPE);
        $helper->Hlblock()->deleteHlblockIfExists(self::TASK_TASK_HL_TYPE);
        $helper->Hlblock()->deleteHlblockIfExists(self::PROMOCODE_HL_TYPE);
        $helper->Hlblock()->deleteHlblockIfExists(self::PET_HL_TYPE);
        $helper->Hlblock()->deleteHlblockIfExists(self::PRIZE_HL_TYPE);
    }

    protected function addPrizeHLBlock(): void
    {
        $helper = new HelperManager();

        $this->prizeHlBlockId = $helper->Hlblock()->addHlblockIfNotExists([
            'NAME' => self::PRIZE_HL_TYPE,
            'TABLE_NAME' => self::PRIZE_HL_TABLE,
            'LANG' => ['ru' => ['NAME' => self::PRIZE_HL_NAME]],
        ]);

        $entityId = 'HLBLOCK_' . $this->prizeHlBlockId;

        $this->prizeHLBlockFieldID = $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_NAME', [
            'FIELD_NAME' => 'UF_NAME',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_NAME',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Название'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Название'],
            'LIST_FILTER_LABEL' => ['ru' => 'Название'],
            'ERROR_MESSAGE' => ['ru' => 'Название'],
            'HELP_MESSAGE' => ['ru' => 'Название'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_IMAGE', [
            'FIELD_NAME' => 'UF_IMAGE',
            'USER_TYPE_ID' => 'file',
            'XML_ID' => 'Название',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'LIST_WIDTH' => 200,
                'LIST_HEIGHT' => 200,
                'MAX_SHOW_SIZE' => 0,
                'MAX_ALLOWED_SIZE' => 0,
                'EXTENSIONS' => [],
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Изображение'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Изображение'],
            'LIST_FILTER_LABEL' => ['ru' => 'Изображение'],
            'ERROR_MESSAGE' => ['ru' => 'Изображение'],
            'HELP_MESSAGE' => ['ru' => 'Изображение'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PRODUCT_XML_ID', [
            'FIELD_NAME' => 'UF_PRODUCT_XML_ID',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_PRODUCT_XML_ID',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Артикул товара'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Артикул товара'],
            'LIST_FILTER_LABEL' => ['ru' => 'Артикул товара'],
            'ERROR_MESSAGE' => ['ru' => 'Артикул товара'],
            'HELP_MESSAGE' => ['ru' => 'Артикул товара'],
        ]);
    }

    protected function addPetHLBlock(): void
    {
        $helper = new HelperManager();

        $this->petHLBlockId = $helper->Hlblock()->addHlblockIfNotExists([
            'NAME' => self::PET_HL_TYPE,
            'TABLE_NAME' => self::PET_HL_TABLE,
            'LANG' => ['ru' => ['NAME' => self::PET_HL_NAME]]
        ]);

        $entityId = 'HLBLOCK_' . $this->petHLBlockId;

        $this->petHLBlockFieldID = $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_NAME', [
            'FIELD_NAME' => 'UF_NAME',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_NAME',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Название животного'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Название животного'],
            'LIST_FILTER_LABEL' => ['ru' => 'Название животного'],
            'ERROR_MESSAGE' => ['ru' => 'Название животного'],
            'HELP_MESSAGE' => ['ru' => 'Название животного'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_IMAGE', [
            'FIELD_NAME' => 'UF_IMAGE',
            'USER_TYPE_ID' => 'file',
            'XML_ID' => 'UF_IMAGE',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'LIST_WIDTH' => 200,
                'LIST_HEIGHT' => 200,
                'MAX_SHOW_SIZE' => 0,
                'MAX_ALLOWED_SIZE' => 0,
                'EXTENSIONS' => [],
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Изображение'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Изображение'],
            'LIST_FILTER_LABEL' => ['ru' => 'Изображение'],
            'ERROR_MESSAGE' => ['ru' => 'Изображение'],
            'HELP_MESSAGE' => ['ru' => 'Изображение'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_DESCRIPTION', [
            'FIELD_NAME' => 'UF_DESCRIPTION',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_DESCRIPTION',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 100,
                'ROWS' => 10,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Описание'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Описание'],
            'LIST_FILTER_LABEL' => ['ru' => 'Описание'],
            'ERROR_MESSAGE' => ['ru' => 'Описание'],
            'HELP_MESSAGE' => ['ru' => 'Описание'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PRIZES', [
            'FIELD_NAME' => 'UF_PRIZES',
            'USER_TYPE_ID' => 'hlblock',
            'XML_ID' => 'UF_PRIZES',
            'SORT' => '100',
            'MULTIPLE' => 'Y',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'DISPLAY' => 'LIST',
                'LIST_HEIGHT' => 5,
                'HLBLOCK_ID' => $this->prizeHlBlockId,
                'HLFIELD_ID' => $this->prizeHLBlockFieldID,
                'DEFAULT_VALUE' => 0,
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Призы'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Призы'],
            'LIST_FILTER_LABEL' => ['ru' => 'Призы'],
            'ERROR_MESSAGE' => ['ru' => 'Призы'],
            'HELP_MESSAGE' => ['ru' => 'Призы'],
        ]);
    }

    protected function addPromocodeHLBlock(): void
    {
        $helper = new HelperManager();

        $this->promocodeHlBlockId = $helper->Hlblock()->addHlblockIfNotExists([
            'NAME' => self::PROMOCODE_HL_TYPE,
            'TABLE_NAME' => self::PROMOCODE_HL_TABLE,
            'LANG' => ['ru' => ['NAME' => self::PROMOCODE_HL_NAME]],
        ]);
        $entityId = 'HLBLOCK_' . $this->promocodeHlBlockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PROMOCODE', [
            'FIELD_NAME' => 'UF_PROMOCODE',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_PROMOCODE',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Промокод'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Промокод'],
            'LIST_FILTER_LABEL' => ['ru' => 'Промокод'],
            'ERROR_MESSAGE' => ['ru' => 'Промокод'],
            'HELP_MESSAGE' => ['ru' => 'Промокод'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_ACTIVE', [
            'FIELD_NAME' => 'UF_ACTIVE',
            'USER_TYPE_ID' => 'boolean',
            'XML_ID' => 'UF_ACTIVE',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'DEFAULT_VALUE' => '1',
                'DISPLAY' => 'CHECKBOX',
                'LABEL' => [
                    0 => '',
                    1 => '',
                ],
                'LABEL_CHECKBOX' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Активность'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Активность'],
            'LIST_FILTER_LABEL' => ['ru' => 'Активность'],
            'ERROR_MESSAGE' => ['ru' => 'Активность'],
            'HELP_MESSAGE' => ['ru' => 'Активность'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PRIZE', [
            'FIELD_NAME' => 'UF_PRIZE',
            'USER_TYPE_ID' => 'hlblock',
            'XML_ID' => 'UF_PRIZE',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'DISPLAY' => 'LIST',
                'LIST_HEIGHT' => 5,
                'HLBLOCK_ID' => $this->prizeHlBlockId,
                'HLFIELD_ID' => $this->prizeHLBlockFieldID,
                'DEFAULT_VALUE' => 0,
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Приз'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Приз'],
            'LIST_FILTER_LABEL' => ['ru' => 'Приз'],
            'ERROR_MESSAGE' => ['ru' => 'Приз'],
            'HELP_MESSAGE' => ['ru' => 'Приз'],
        ]);
    }

    /**
     * @throws IblockNotFoundException
     */
    protected function addTaskHlBlock(): void
    {
        $helper = new HelperManager();

        $this->taskHlBlockId = $helper->Hlblock()->addHlblockIfNotExists([
            'NAME' => self::TASK_TASK_HL_TYPE,
            'TABLE_NAME' => self::TASK_HL_TABLE,
            'LANG' => ['ru' => ['NAME' => self::TASK_HL_NAME]],
        ]);
        $entityId = 'HLBLOCK_' . $this->taskHlBlockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PET', [
            'FIELD_NAME' => 'UF_PET',
            'USER_TYPE_ID' => 'hlblock',
            'XML_ID' => 'UF_PET',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'DISPLAY' => 'LIST',
                'LIST_HEIGHT' => 5,
                'HLBLOCK_ID' => $this->petHLBlockId,
                'HLFIELD_ID' => $this->petHLBlockFieldID,
                'DEFAULT_VALUE' => 0,
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Питомец'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Питомец'],
            'LIST_FILTER_LABEL' => ['ru' => 'Питомец'],
            'ERROR_MESSAGE' => ['ru' => 'Питомец'],
            'HELP_MESSAGE' => ['ru' => 'Питомец'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_TITLE', [
            'FIELD_NAME' => 'UF_TITLE',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_TITLE',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Заголовок'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Заголовок'],
            'LIST_FILTER_LABEL' => ['ru' => 'Заголовок'],
            'ERROR_MESSAGE' => ['ru' => 'Заголовок'],
            'HELP_MESSAGE' => ['ru' => 'Заголовок'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_TASK', [
            'FIELD_NAME' => 'UF_TASK',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_TASK',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 100,
                'ROWS' => 2,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Задание'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Задание'],
            'LIST_FILTER_LABEL' => ['ru' => 'Задание'],
            'ERROR_MESSAGE' => ['ru' => 'Задание'],
            'HELP_MESSAGE' => ['ru' => 'Задание'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_IMAGE', [
            'FIELD_NAME' => 'UF_IMAGE',
            'USER_TYPE_ID' => 'file',
            'XML_ID' => 'UF_IMAGE',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'LIST_WIDTH' => 200,
                'LIST_HEIGHT' => 200,
                'MAX_SHOW_SIZE' => 0,
                'MAX_ALLOWED_SIZE' => 0,
                'EXTENSIONS' => [],
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Изображение'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Изображение'],
            'LIST_FILTER_LABEL' => ['ru' => 'Изображение'],
            'ERROR_MESSAGE' => ['ru' => 'Изображение'],
            'HELP_MESSAGE' => ['ru' => 'Изображение'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_CATEGORY', [
            'FIELD_NAME' => 'UF_CATEGORY',
            'USER_TYPE_ID' => 'iblock_section',
            'XML_ID' => 'UF_CATEGORY',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'DISPLAY' => 'LIST',
                'LIST_HEIGHT' => 5,
                'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
                'DEFAULT_VALUE' => '',
                'ACTIVE_FILTER' => 'N',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Раздел товара'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Раздел товара'],
            'LIST_FILTER_LABEL' => ['ru' => 'Раздел товара'],
            'ERROR_MESSAGE' => ['ru' => 'Раздел товара'],
            'HELP_MESSAGE' => ['ru' => 'Раздел товара'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PRODUCT_XML_ID', [
            'FIELD_NAME' => 'UF_PRODUCT_XML_ID',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_PRODUCT_XML_ID',
            'SORT' => '100',
            'MULTIPLE' => 'Y',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Артикулы товаров'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Артикулы товаров'],
            'LIST_FILTER_LABEL' => ['ru' => 'Артикулы товаров'],
            'ERROR_MESSAGE' => ['ru' => 'Артикулы товаров'],
            'HELP_MESSAGE' => ['ru' => 'Артикулы товаров'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_QUESTION', [
            'FIELD_NAME' => 'UF_QUESTION',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_QUESTION',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 100,
                'ROWS' => 2,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Вопрос'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Вопрос'],
            'LIST_FILTER_LABEL' => ['ru' => 'Вопрос'],
            'ERROR_MESSAGE' => ['ru' => 'Вопрос'],
            'HELP_MESSAGE' => ['ru' => 'Вопрос'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_VARIANTS', [
            'FIELD_NAME' => 'UF_VARIANTS',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_VARIANTS',
            'SORT' => '100',
            'MULTIPLE' => 'Y',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Варианты ответа'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Варианты ответа'],
            'LIST_FILTER_LABEL' => ['ru' => 'Варианты ответа'],
            'ERROR_MESSAGE' => ['ru' => 'Варианты ответа'],
            'HELP_MESSAGE' => ['ru' => 'Варианты ответа'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_ANSWER', [
            'FIELD_NAME' => 'UF_ANSWER',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_ANSWER',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Правильный ответ'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Правильный ответ'],
            'LIST_FILTER_LABEL' => ['ru' => 'Правильный ответ'],
            'ERROR_MESSAGE' => ['ru' => 'Правильный ответ'],
            'HELP_MESSAGE' => ['ru' => 'Правильный ответ'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_CORRECT_TEXT', [
            'FIELD_NAME' => 'UF_CORRECT_TEXT',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_CORRECT_TEXT',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 100,
                'ROWS' => 2,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Интересный факт'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Интересный факт'],
            'LIST_FILTER_LABEL' => ['ru' => 'Интересный факт'],
            'ERROR_MESSAGE' => ['ru' => 'Интересный факт'],
            'HELP_MESSAGE' => ['ru' => 'Интересный факт'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_QUESTION_ERROR', [
            'FIELD_NAME' => 'UF_QUESTION_ERROR',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_QUESTION_ERROR',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 100,
                'ROWS' => 2,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Поясняющий текст для вопроса'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Поясняющий текст для вопроса'],
            'LIST_FILTER_LABEL' => ['ru' => 'Поясняющий текст для вопроса'],
            'ERROR_MESSAGE' => ['ru' => 'Поясняющий текст для вопроса'],
            'HELP_MESSAGE' => ['ru' => 'Поясняющий текст для вопроса'],
        ]);
    }

    protected function addUserResultHLBlock(): void
    {
        $helper = new HelperManager();

        $this->userResultHlBlockId = $helper->Hlblock()->addHlblockIfNotExists([
            'NAME' => self::USER_RESULT_HL_TYPE,
            'TABLE_NAME' => self::USER_RESULT_HL_TABLE,
            'LANG' => ['ru' => ['NAME' => self::USER_RESULT_HL_NAME]],
        ]);
        $entityId = 'HLBLOCK_' . $this->userResultHlBlockId;

        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_USER_ID', [
            'FIELD_NAME' => 'UF_USER_ID',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_USER_ID',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'Y',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'ID пользователя'],
            'LIST_COLUMN_LABEL' => ['ru' => 'ID пользователя'],
            'LIST_FILTER_LABEL' => ['ru' => 'ID пользователя'],
            'ERROR_MESSAGE' => ['ru' => 'ID пользователя'],
            'HELP_MESSAGE' => ['ru' => 'ID пользователя'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PET', [
            'FIELD_NAME' => 'UF_PET',
            'USER_TYPE_ID' => 'hlblock',
            'XML_ID' => 'UF_PET',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'DISPLAY' => 'LIST',
                'LIST_HEIGHT' => 5,
                'HLBLOCK_ID' => $this->petHLBlockId,
                'HLFIELD_ID' => $this->petHLBlockFieldID,
                'DEFAULT_VALUE' => 0,
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Питомец'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Питомец'],
            'LIST_FILTER_LABEL' => ['ru' => 'Питомец'],
            'ERROR_MESSAGE' => ['ru' => 'Питомец'],
            'HELP_MESSAGE' => ['ru' => 'Питомец'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_TASKS', [
            'FIELD_NAME' => 'UF_TASKS',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_TASKS',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Задания квеста'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Задания квеста'],
            'LIST_FILTER_LABEL' => ['ru' => 'Задания квеста'],
            'ERROR_MESSAGE' => ['ru' => 'Задания квеста'],
            'HELP_MESSAGE' => ['ru' => 'Задания квеста'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_CURRENT_TASK', [
            'FIELD_NAME' => 'UF_CURRENT_TASK',
            'USER_TYPE_ID' => 'integer',
            'XML_ID' => 'UF_CURRENT_TASK',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'MIN_VALUE' => 0,
                'MAX_VALUE' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Текущее задание'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Текущее задание'],
            'LIST_FILTER_LABEL' => ['ru' => 'Текущее задание'],
            'ERROR_MESSAGE' => ['ru' => 'Текущее задание'],
            'HELP_MESSAGE' => ['ru' => 'Текущее задание'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PRIZE', [
            'FIELD_NAME' => 'UF_PRIZE',
            'USER_TYPE_ID' => 'hlblock',
            'XML_ID' => 'UF_PRIZE',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'DISPLAY' => 'LIST',
                'LIST_HEIGHT' => 5,
                'HLBLOCK_ID' => $this->prizeHlBlockId,
                'HLFIELD_ID' => $this->prizeHLBlockFieldID,
                'DEFAULT_VALUE' => 0,
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Выбранный приз'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Выбранный приз'],
            'LIST_FILTER_LABEL' => ['ru' => 'Выбранный приз'],
            'ERROR_MESSAGE' => ['ru' => 'Выбранный приз'],
            'HELP_MESSAGE' => ['ru' => 'Выбранный приз'],
        ]);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_PROMOCODE', [
            'FIELD_NAME' => 'UF_PROMOCODE',
            'USER_TYPE_ID' => 'string',
            'XML_ID' => 'UF_PROMOCODE',
            'SORT' => '100',
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'N',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS' => [
                'SIZE' => 20,
                'ROWS' => 1,
                'REGEXP' => '',
                'MIN_LENGTH' => 0,
                'MAX_LENGTH' => 0,
                'DEFAULT_VALUE' => '',
            ],
            'EDIT_FORM_LABEL' => ['ru' => 'Промокод'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Промокод'],
            'LIST_FILTER_LABEL' => ['ru' => 'Промокод'],
            'ERROR_MESSAGE' => ['ru' => 'Промокод'],
            'HELP_MESSAGE' => ['ru' => 'Промокод'],
        ]);
    }
}
