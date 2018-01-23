<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;

class Iblock_food_selection20180122230845 extends SprintMigrationBase
{
    
    protected $description = '';
    
    /** @var HelperManager $helper */
    private $helper;
    
    private $iblockId;
    
    public function up()
    {
        $helper       = new HelperManager();
        $this->helper = $helper;
        
        $helper->Iblock()->addIblockTypeIfNotExists(
            [
                'ID'               => 'catalog',
                'SECTIONS'         => 'Y',
                'EDIT_FILE_BEFORE' => '',
                'EDIT_FILE_AFTER'  => '',
                'IN_RSS'           => 'N',
                'SORT'             => '100',
                'LANG'             => [
                    'ru' => [
                        'NAME'         => 'Каталог',
                        'SECTION_NAME' => '',
                        'ELEMENT_NAME' => '',
                    ],
                ],
            ]
        );
        
        $iblockId = $helper->Iblock()->addIblockIfNotExists(
            [
                'IBLOCK_TYPE_ID'     => 'catalog',
                'LID'                => 's1',
                'CODE'               => 'food_selection',
                'NAME'               => 'Подбор корма',
                'ACTIVE'             => 'Y',
                'SORT'               => '500',
                'LIST_PAGE_URL'      => '#SITE_DIR#/catalog/food-selection/',
                'DETAIL_PAGE_URL'    => '#SITE_DIR#/catalog/food-selection/#SECTION_CODE_PATH#/#ELEMENT_CODE#/',
                'SECTION_PAGE_URL'   => '#SITE_DIR#/catalog/food-selection/#SECTION_CODE_PATH#/',
                'CANONICAL_PAGE_URL' => 'http://#SERVER_NAME##SITE_DIR#/catalog/food-selection/#SECTION_CODE_PATH#/#ELEMENT_CODE#/',
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
                'TMP_ID'             => '507e7bffa0ceb3151f0ca2421689420e',
                'INDEX_ELEMENT'      => 'Y',
                'INDEX_SECTION'      => 'Y',
                'WORKFLOW'           => 'N',
                'BIZPROC'            => 'N',
                'SECTION_CHOOSER'    => 'L',
                'LIST_MODE'          => '',
                'RIGHTS_MODE'        => 'S',
                'SECTION_PROPERTY'   => null,
                'PROPERTY_INDEX'     => null,
                'VERSION'            => '1',
                'LAST_CONV_ELEMENT'  => '0',
                'SOCNET_GROUP_ID'    => null,
                'EDIT_FILE_BEFORE'   => '',
                'EDIT_FILE_AFTER'    => '',
                'SECTIONS_NAME'      => 'Разделы',
                'SECTION_NAME'       => 'Раздел',
                'ELEMENTS_NAME'      => 'Элементы',
                'ELEMENT_NAME'       => 'Элемент',
                'EXTERNAL_ID'        => '',
                'LANG_DIR'           => '/',
                'SERVER_NAME'        => '4lapy.ru',
            ]
        );
        
        $this->iblockId = $iblockId;
        
        $helper->Iblock()->updateIblockFields(
            $iblockId,
            [
                'IBLOCK_SECTION'    => [
                    'NAME'          => 'Привязка к разделам',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => [
                        'KEEP_IBLOCK_SECTION_ID' => 'N',
                    ],
                ],
                'ACTIVE'            => [
                    'NAME'          => 'Активность',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => 'Y',
                ],
                'ACTIVE_FROM'       => [
                    'NAME'          => 'Начало активности',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
                'ACTIVE_TO'         => [
                    'NAME'          => 'Окончание активности',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
                'SORT'              => [
                    'NAME'          => 'Сортировка',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '0',
                ],
                'NAME'              => [
                    'NAME'          => 'Название',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => '',
                ],
                'PREVIEW_PICTURE'   => [
                    'NAME'          => 'Картинка для анонса',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => [
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
                'PREVIEW_TEXT_TYPE' => [
                    'NAME'          => 'Тип описания для анонса',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => 'text',
                ],
                'PREVIEW_TEXT'      => [
                    'NAME'          => 'Описание для анонса',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
                'DETAIL_PICTURE'    => [
                    'NAME'          => 'Детальная картинка',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => [
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
                'DETAIL_TEXT_TYPE'  => [
                    'NAME'          => 'Тип детального описания',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => 'text',
                ],
                'DETAIL_TEXT'       => [
                    'NAME'          => 'Детальное описание',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
                'XML_ID'            => [
                    'NAME'          => 'Внешний код',
                    'IS_REQUIRED'   => 'Y',
                    'DEFAULT_VALUE' => '',
                ],
                'CODE'              => [
                    'NAME'          => 'Символьный код',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => [
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
                'TAGS'              => [
                    'NAME'          => 'Теги',
                    'IS_REQUIRED'   => 'N',
                    'DEFAULT_VALUE' => '',
                ],
            ]
        );
        
        $helper->Iblock()->addPropertyIfNotExists(
            $iblockId,
            [
                'NAME'               => 'Привязка к товару',
                'ACTIVE'             => 'Y',
                'SORT'               => '500',
                'CODE'               => 'ITEM',
                'DEFAULT_VALUE'      => '',
                'PROPERTY_TYPE'      => 'E',
                'ROW_COUNT'          => '1',
                'COL_COUNT'          => '30',
                'LIST_TYPE'          => 'L',
                'MULTIPLE'           => 'N',
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
                'USER_TYPE'          => 'EAutocomplete',
                'USER_TYPE_SETTINGS' => [
                    'VIEW'          => 'A',
                    'SHOW_ADD'      => 'N',
                    'MAX_WIDTH'     => 0,
                    'MIN_HEIGHT'    => 24,
                    'MAX_HEIGHT'    => 1000,
                    'BAN_SYM'       => ',;',
                    'REP_SYM'       => ' ',
                    'OTHER_REP_SYM' => '',
                    'IBLOCK_MESS'   => 'N',
                ],
                'HINT'               => '',
            ]
        );
        
        /** add sections */
        //Кошка
        $catId = $helper->Iblock()->addSectionIfNotExists(
            $iblockId,
            [
                'ACTIVE'            => 'Y',
                'IBLOCK_SECTION_ID' => false,
                'NAME'              => 'Кошка',
                'SORT'              => 100,
            ]
        );
        //Возраст питомца
        $this->addSection('Юниор (до 1 года)', 'pet_age', $catId);
        $this->addSection('Эдалт (от 1 до 3 лет)', 'pet_age', $catId);
        $this->addSection('Сеньор (больше 3-х лет)', 'pet_age', $catId);
        //Специализация корма
        try {
            $dataManager = HLBlockFactory::createTableObject('Purpose');
            $res = $dataManager::query()->setSelect(['UF_NAME'])->whereNotNull('UF_NAME')->exec();
            while($item = $res->fetch()){
                $this->addSection($item['UF_NAME'], 'purpose', $catId);
            }
        } catch (\Exception $e) {
        }
        //Особенности ингредиентов
        try {
            $dataManager = HLBlockFactory::createTableObject('IngridientFeatures');
            $res = $dataManager::query()->setSelect(['UF_NAME'])->whereNotNull('UF_NAME')->exec();
            while($item = $res->fetch()){
                $this->addSection($item['UF_NAME'], 'ingridient', $catId);
            }
        } catch (\Exception $e) {
        }
        //Тип корма
        try {
            $dataManager = HLBlockFactory::createTableObject('Consistence');
            $res = $dataManager::query()->setSelect(['UF_NAME'])->whereNotNull('UF_NAME')->exec();
            while($item = $res->fetch()){
                $this->addSection($item['UF_NAME'], 'consistence', $catId);
            }
        } catch (\Exception $e) {
        }
        //Вкус корма
        try {
            $dataManager = HLBlockFactory::createTableObject('Flavour');
            $res = $dataManager::query()->setSelect(['UF_NAME'])->whereNotNull('UF_NAME')->exec();
            while($item = $res->fetch()){
                $this->addSection($item['UF_NAME'], 'flavour', $catId);
            }
        } catch (\Exception $e) {
        }
        
        //Собака
        $dogId = $helper->Iblock()->addSectionIfNotExists(
            $iblockId,
            [
                'ACTIVE'            => 'Y',
                'IBLOCK_SECTION_ID' => false,
                'NAME'              => 'Собака',
                'CODE'              => 'dog',
                'SORT'              => 200,
            ]
        );
        //Возраст питомца
        $this->addSection('Юниор (до 1 года)', 'pet_age', $dogId);
        $this->addSection('Эдалт (от 1 до 3 лет)', 'pet_age', $dogId);
        $this->addSection('Сеньор (больше 3-х лет)', 'pet_age', $dogId);
        //Размер питомца
        $this->addSection('Мелкий', 'pet_size', $dogId);
        $this->addSection('Средний', 'pet_size', $dogId);
        $this->addSection('Крупный', 'pet_size', $dogId);
        //Специализация корма
        try {
            $dataManager = HLBlockFactory::createTableObject('Purpose');
            $res = $dataManager::query()->setSelect(['UF_NAME'])->whereNotNull('UF_NAME')->exec();
            while($item = $res->fetch()){
                $this->addSection($item['UF_NAME'], 'food_purpose', $dogId);
            }
        } catch (\Exception $e) {
        }
        //Особенности ингредиентов
        try {
            $dataManager = HLBlockFactory::createTableObject('IngridientFeatures');
            $res = $dataManager::query()->setSelect(['UF_NAME'])->whereNotNull('UF_NAME')->exec();
            while($item = $res->fetch()){
                $this->addSection($item['UF_NAME'], 'food_ingridient', $dogId);
            }
        } catch (\Exception $e) {
        }
        //Тип корма
        try {
            $dataManager = HLBlockFactory::createTableObject('Consistence');
            $res = $dataManager::query()->setSelect(['UF_NAME'])->whereNotNull('UF_NAME')->exec();
            while($item = $res->fetch()){
                $this->addSection($item['UF_NAME'], 'food_consistence', $dogId);
            }
        } catch (\Exception $e) {
        }
        //Вкус корма
        try {
            $dataManager = HLBlockFactory::createTableObject('Flavour');
            $res = $dataManager::query()->setSelect(['UF_NAME'])->whereNotNull('UF_NAME')->exec();
            while($item = $res->fetch()){
                $this->addSection($item['UF_NAME'], 'food_flavour', $dogId);
            }
        } catch (\Exception $e) {
        }
    }
    
    private function addSection(string $name, string $type, int $iblockSectionId = 0)
    {
        $data = [
            'ACTIVE'            => 'Y',
            'IBLOCK_SECTION_ID' => $iblockSectionId === 0 ? false : $iblockSectionId,
            'NAME'              => $name,
        ];
        if (!empty($type)) {
            $data['XML_ID'] = $type;
        }
        $data['CODE'] = \CUtil::translit($data['NAME'], 'ru');
        $this->helper->Iblock()->addSectionIfNotExists(
            $this->iblockId,
            $data
        );
    }
    
    public function down()
    {
        $helper = new HelperManager();
        
        //your code ...
        
    }
    
}
