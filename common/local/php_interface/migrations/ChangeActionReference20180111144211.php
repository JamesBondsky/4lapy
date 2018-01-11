<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class ChangeActionReference20180111144211 extends SprintMigrationBase
{
    
    protected $description = 'Замена справочника типа для акций';
    
    public function up()
    {
        $helper = new HelperManager();
        
        $iblockId = $helper->Iblock()->getIblockId('shares', 'publications');
        
        $helper->Iblock()->updatePropertyIfExists($iblockId,
                                                  'TYPE',
                                                  [
                                                      'NAME'               => 'Тип',
                                                      'ACTIVE'             => 'Y',
                                                      'SORT'               => '20',
                                                      'CODE'               => 'TYPE',
                                                      'DEFAULT_VALUE'      => '',
                                                      'PROPERTY_TYPE'      => 'S',
                                                      'ROW_COUNT'          => '1',
                                                      'COL_COUNT'          => '30',
                                                      'LIST_TYPE'          => 'L',
                                                      'MULTIPLE'           => 'Y',
                                                      'XML_ID'             => '',
                                                      'FILE_TYPE'          => '',
                                                      'MULTIPLE_CNT'       => '5',
                                                      'TMP_ID'             => null,
                                                      'LINK_IBLOCK_ID'     => '0',
                                                      'WITH_DESCRIPTION'   => 'N',
                                                      'SEARCHABLE'         => 'N',
                                                      'FILTRABLE'          => 'N',
                                                      'IS_REQUIRED'        => 'N',
                                                      'VERSION'            => '1',
                                                      'USER_TYPE'          => 'directory',
                                                      'USER_TYPE_SETTINGS' => [
                                                          'size'       => 1,
                                                          'width'      => 0,
                                                          'group'      => 'N',
                                                          'multiple'   => 'N',
                                                          'TABLE_NAME' => 'b_hlbd_publicationtype',
                                                      ],
                                                      'HINT'               => '',
                                                  ]);
    }
    
    public function down()
    {
        /**
         * Нет необходимости, миграция исправляет ошибку
         */
    }
    
}
