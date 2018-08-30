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

class CatalogCategoryLandingArticlesField20180830182203 extends SprintMigrationBase
{

    public const ENTITY_ID = 'IBLOCK_2_SECTION';
    protected $description = 'Свойство привязки к разделу публикаций для лендинга';

    protected $field = [
        'FIELD_NAME'        => 'UF_LANDING_ARTICLES',
        'USER_TYPE_ID'      => 'iblock_section',
        'XML_ID'            => 'UF_LANDING_ARTICLES',
        'SORT'              => '100',
        'MULTIPLE'          => 'N',
        'MANDATORY'         => 'N',
        'SHOW_FILTER'       => 'N',
        'SHOW_IN_LIST'      => 'Y',
        'EDIT_IN_LIST'      => 'Y',
        'IS_SEARCHABLE'     => 'N',
        'SETTINGS'          => [
            'DISPLAY'       => 'LIST',
            'LIST_HEIGHT'   => 5,
            'DEFAULT_VALUE' => '',
            'ACTIVE_FILTER' => 'N',
        ],
        'EDIT_FORM_LABEL'   => [
            'ru' => 'Раздел статей для лендинга',
        ],
        'LIST_COLUMN_LABEL' => [
            'ru' => 'Раздел статей для лендинга',
        ],
        'LIST_FILTER_LABEL' => [
            'ru' => 'Раздел статей для лендинга',
        ],
    ];

    /**
     * @return bool
     * @throws IblockNotFoundException
     * @throws Exceptions\HelperException
     */
    public function up(): bool
    {
        $field = $this->field;
        $field['SETTINGS']['IBLOCK_ID'] = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::ARTICLES);

        if ($this->getHelper()->UserTypeEntity()->addUserTypeEntityIfNotExists(static::ENTITY_ID, $field['FIELD_NAME'], $field)) {
            $this->log()->info('Пользовательское свойство ' . $field['FIELD_NAME'] . ' создано');
        } else {
            $this->log()->error('Ошибка при создании пользовательского свойства ' . $field['FIELD_NAME']);

            return false;
        }

        return true;
    }

    /**
     * @return bool
     * @throws Exceptions\HelperException
     */
    public function down()
    {
        if ($this->getHelper()->UserTypeEntity()->deleteUserTypeEntityIfExists(static::ENTITY_ID, $this->field['FIELD_NAME'])) {
            $this->log()->info('Пользовательское свойство ' . $this->field['CODE'] . ' удалено');
        } else {
            $this->log()->error('Ошибка при удалении пользовательского свойства ' . $this->field['FIELD_NAME']);

            return false;
        }

        return true;
    }
}
