<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;

class Reference_codes_20180101224518 extends SprintMigrationBase
{
    
    protected $description = 'Set reference codes from SAP';
    
    public function parseFile() {
        $prepared = [];
        
        $csv = fopen(__DIR__ . '/reference_codes.csv', 'r');
        
        while ($row = fgetcsv($csv, 0, ';')) {
            if ($row[0] === 'hl') {
                $key = $row[1];
            } elseif (null !== $key) {
                $prepared[$key][] = [
                    'UF_XML_ID' => $row[0],
                    'UF_NAME'   => $row[1],
                ];
            }
        }
        
        return $prepared;
    }
    
    /**
     * @return bool|void
     * @throws \Exception
     */
    public function up() {
        $result = $this->parseFile();
        
        foreach ($result as $hlBlock => $values) {
            $this->addUf($hlBlock);
            $this->addValues($hlBlock, $values);
        }
    }
    
    /**
     * @param string $hlBlockCode
     * @param array  $values
     *
     * @throws \Exception
     */
    public function addValues(string $hlBlockCode, array $values) {
        $hlEntity = HLBlockFactory::createTableObject($hlBlockCode);
        
        $currentValues = $hlEntity::getList([
                                                'select' => [
                                                    'UF_NAME',
                                                    'UF_XML_ID',
                                                    'UF_CODE',
                                                    'ID',
                                                ],
                                            ])->fetchAll();
        
        $text = array_column($currentValues, 'UF_NAME');
        
        foreach ($values as $value) {
            $current = $currentValues[array_search($value['UF_NAME'], $text)];
            
            if ($current['ID']) {
                $hlEntity::update($current['ID'], [
                    'UF_CODE'   => $current['UF_XML_ID'],
                    'UF_XML_ID' => $value['UF_XML_ID'],
                ]);
            } else {
                $hlEntity::add([
                                   'UF_NAME'   => $value['UF_NAME'],
                                   'UF_XML_ID' => $value['UF_XML_ID'],
                                   'UF_CODE'   => \CUtil::translit($value['UF_NAME'], 'ru', [
                                       'max_len'               => '255',
                                       'change_case'           => 'L',
                                       'replace_space'         => '-',
                                       'replace_other'         => '-',
                                       'delete_repeat_replace' => 'true',
                                   ]),
                               ]);
                
                $this->logger->info(sprintf('Entity was added: %s (%s) to %s', $value['UF_NAME'], $value['UF_XML_ID'],
                                            $hlBlockCode));
            }
        }
    }
    
    /**
     * @param string $hlBlockCode
     */
    public function addUf(string $hlBlockCode) {
        $helper = new HelperManager();
        
        $hlblockId = $helper->Hlblock()->getHlblockId($hlBlockCode);
        $entityId  = 'HLBLOCK_' . $hlblockId;
        
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists($entityId, 'UF_CODE', [
            'FIELD_NAME'        => 'UF_CODE',
            'USER_TYPE_ID'      => 'string',
            'XML_ID'            => '',
            'SORT'              => '200',
            'MULTIPLE'          => 'N',
            'MANDATORY'         => 'Y',
            'SHOW_FILTER'       => 'N',
            'SHOW_IN_LIST'      => 'Y',
            'EDIT_IN_LIST'      => 'Y',
            'IS_SEARCHABLE'     => 'N',
            'SETTINGS'          => [
                'SIZE'          => 20,
                'ROWS'          => 1,
                'REGEXP'        => null,
                'MIN_LENGTH'    => 0,
                'MAX_LENGTH'    => 0,
                'DEFAULT_VALUE' => null,
            ],
            'EDIT_FORM_LABEL'   => [
                'ru' => 'Название',
            ],
            'LIST_COLUMN_LABEL' => [
                'ru' => 'Название',
            ],
            'LIST_FILTER_LABEL' => [
                'ru' => 'Название',
            ],
            'ERROR_MESSAGE'     => [
                'ru' => null,
            ],
            'HELP_MESSAGE'      => [
                'ru' => null,
            ],
        ]);
    }
    
    public function down() {
        $helper = new HelperManager();
        
        //your code ...
        
    }
    
}
