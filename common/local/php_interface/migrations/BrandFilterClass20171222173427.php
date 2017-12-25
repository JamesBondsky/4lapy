<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Highloadblock\DataManager;
use FourPaws\App\Application;

class BrandFilterClass20171222173427 extends SprintMigrationBase
{
    public function __construct()
    {
        parent::__construct();
        $this->description = 'Миграция для свойства UF_CODE';
    }

    public function up()
    {
        $helper = new HelperManager();


        $hlId = $helper->Hlblock()->getHlblockId('Filter');
        $entityId = 'HLBLOCK_' . $helper->Hlblock()->getHlblockId($hlId);
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_CODE', [
            'FIELD_NAME'        => 'UF_CODE',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => 'UF_CODE',
            'SORT'              => '500',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'S',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          =>
                [
                    'SIZE'          => 20,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'en' => '',
                    'ru' => 'Символьный код',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'en' => '',
                    'ru' => 'Символьный код',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'en' => '',
                    'ru' => 'Символьный код',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'en' => '',
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'en' => '',
                    'ru' => '',
                ],
        ]);

        return $this->setForCurrent();
    }

    protected function setForCurrent()
    {
        /**
         * @var DataManager $dataManager
         */
        $dataManager = Application::getInstance()->getContainer()->get('bx.hlblock.filter');

        $result = $dataManager::query()
            ->addSelect('UF_CLASS_NAME')
            ->addSelect('ID')
            ->exec();

        while ($data = $result->fetch()) {
            if (\is_array($data)) {
                /**
                 * @var array $data
                 */
                $updateResult = $dataManager::update($data['ID'], [
                    'UF_CODE' => $this->getCodeForClass($data['UF_CLASS_NAME']),
                ]);

                if (!$updateResult->isSuccess()) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function getCodeForClass(string $className)
    {
        $parts = explode('\\', trim($className, '\\'));
        $name = array_pop($parts);
        $chars = strpos($name, 'Filter');
        $name = substr($name, 0, $chars);

        return $this->fromCamelCase($name);
    }

    protected function fromCamelCase(string $input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = strtoupper($match);
        }
        return implode('_', $ret);
    }
}
