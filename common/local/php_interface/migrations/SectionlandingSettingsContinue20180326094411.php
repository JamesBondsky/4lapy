<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Main\Application;
use FourPaws\BitrixOrm\Model\IblockSect;
use FourPaws\BitrixOrm\Query\IblockSectQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class SectionlandingSettingsContinue20180326094411 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Установка свойства показа в лендинге в новостях и статьях, интерфейс формы, раздел-лендинг';

    public function up()
    {
        $helper = new HelperManager();

        $faqIblock = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::FAQ);
        $newsIblock = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::NEWS);
        $articlesIblock = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::ARTICLES);
        $bannerIblock = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS);
        $catalogIblock = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);


        $this->log()->info('Установка свойств IN_LANDING');
        $helper->Iblock()->addPropertyIfNotExists($newsIblock, [
            'NAME'               => 'Показывать в лендинге',
            'ACTIVE'             => 'Y',
            'SORT'               => '30',
            'CODE'               => 'IN_LANDING',
            'DEFAULT_VALUE'      => 0,
            'PROPERTY_TYPE'      => 'N',
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
            'FILTRABLE'          => 'Y',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '2',
            'USER_TYPE'          => 'YesNoPropertyType',
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
        ]);

        $helper->Iblock()->addPropertyIfNotExists($articlesIblock, [
            'NAME'               => 'Показывать в лендинге',
            'ACTIVE'             => 'Y',
            'SORT'               => '30',
            'CODE'               => 'IN_LANDING',
            'DEFAULT_VALUE'      => 0,
            'PROPERTY_TYPE'      => 'N',
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
            'FILTRABLE'          => 'Y',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '2',
            'USER_TYPE'          => 'YesNoPropertyType',
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
        ]);
        $this->log()->info('свосйства IN_LANDING установлены');

        $this->log()->info('добавление в раздел "защита от блох и клещей" првязку к разделу FAQ');
        $helper->UserTypeEntity()->addUserTypeEntityIfNotExists('IBLOCK_'.$catalogIblock.'_SECTION', 'UF_FAQ_SECTION', [
            'ENTITY_ID'         => 'IBLOCK_'.$catalogIblock.'_SECTION',
            'FIELD_NAME'        => 'UF_FAQ_SECTION',
            'USER_TYPE_ID'      => 'iblock_section',
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
                    'DISPLAY'       => 'LIST',
                    'LIST_HEIGHT'   => 5,
                    'IBLOCK_ID'     => $faqIblock,
                    'DEFAULT_VALUE' => '',
                    'ACTIVE_FILTER' => 'Y',
                ],
            'EDIT_FORM_LABEL'   =>
                [
                    'ru' => 'Привязка к faq',
                ],
            'LIST_COLUMN_LABEL' =>
                [
                    'ru' => 'Привязка к faq',
                ],
            'LIST_FILTER_LABEL' =>
                [
                    'ru' => 'Привязка к faq',
                ],
            'ERROR_MESSAGE'     =>
                [
                    'ru' => '',
                ],
            'HELP_MESSAGE'      =>
                [
                    'ru' => 'Привязка к faq',
                ],
        ]);
        $this->log()->info('в раздел "защита от блох и клещей" добавлена првязку к разделу FAQ');

        $html = '<div class="b-fleas-protection-banner__title">Узнайте о способах защиты и лечения питомцев от блох и
                        клещей
                    </div>
                    <div class="b-fleas-protection-banner__doctor">
                        <div class="b-fleas-protection-banner__doctor-img"><img src="./images/content/img_doc.png"/>
                        </div>
                        <div class="b-fleas-protection-banner__doctor-info">
                            <div class="b-fleas-protection-banner__doctor-name">Андрей Михайлович Семеренко</div>
                            <div class="b-fleas-protection-banner__doctor-type">ведущий ветеринарный врач</div>
                        </div>
                    </div>
                    <div class="b-fleas-protection-banner__button js-form-move"><span>Задать вопрос</span></div>';
        $faqSection = (new IblockSectQuery())->withFilter(['CODE'=>'fleas'])->exec()->first()->getId();
        $isLanding = true;
        $sections = [
            351 => [
                'NEW_SECT'          => 538,
                'UF_FAQ_SECTION'    => $faqSection,
                'UF_LANDING'        => $isLanding,
                'UF_LANDING_BANNER' => $html,
            ],
            //ветеринарная аптека с верхнего уровня
        ];
        $iblockSect = new \CIBlockSection();

        $this->log()->info('перенос раздела "защита от блох и клещей" в раздел "ветеринарная аптека"');
        $sectId = 351;
        $iblockSect->Update($sectId, ['IBLOCK_SECTION_ID' => $sections[$sectId]['NEW_SECT']]);
        $this->log()->info('перенос раздела "защита от блох и клещей" в раздел "ветеринарная аптека" завершен');

        $this->log()->info('Установка привязки к FAQ для раздела "защита от блох и клещей"');
        foreach ($sections as $sectId => $sect) {
            $iblockSect->Update($sectId, [
                'UF_FAQ_SECTION'    => $sect['UF_FAQ_SECTION'],
                'UF_LANDING'        => $sect['UF_LANDING'],
                'UF_LANDING_BANNER' => $sect['UF_LANDING_BANNER'],
            ]);
        }
        $this->log()->info('Установка привязки к FAQ для раздела "защита от блох и клещей" завершена');

        $this->log()->info('Начало установки интерфейсов');
        //форма элемента news
        $this->getHelper()->AdminIblock()->buildElementForm(
            $newsIblock,
            [
                'Новость'  => [
                    'ID',
                    'DATE_CREATE',
                    'TIMESTAMP_X',
                    'ACTIVE',
                    'ACTIVE_FROM',
                    'ACTIVE_TO',
                    'NAME',
                    'CODE',
                    'XML_ID',
                    'SORT',
                    'PROPERTY_TYPE',
                    'PROPERTY_PUBLICATION_TYPE',
                    'PROPERTY_PRODUCTS',
                    'PROPERTY_VIDEO',
                    'PROPERTY_MORE_PHOTO',
                    'PROPERTY_IN_LANDING', //Добавлено
                    'PROPERTY_OLD_URL', //Добавлено
                ],
                'Анонс'    => [
                    'PREVIEW_PICTURE',
                    'PREVIEW_TEXT',
                ],
                'Подробно' => [
                    'DETAIL_PICTURE',
                    'DETAIL_TEXT',
                ],
                'SEO'      => [
                    'IPROPERTY_TEMPLATES_ELEMENT_META_TITLE',
                    'IPROPERTY_TEMPLATES_ELEMENT_META_KEYWORDS',
                    'IPROPERTY_TEMPLATES_ELEMENT_META_DESCRIPTION',
                    'IPROPERTY_TEMPLATES_ELEMENT_PAGE_TITLE',
                    'IPROPERTY_TEMPLATES_ELEMENTS_PREVIEW_PICTURE',
                    'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_ALT',
                    'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_TITLE',
                    'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_NAME',
                    'IPROPERTY_TEMPLATES_ELEMENTS_DETAIL_PICTURE',
                    'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_ALT',
                    'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_TITLE',
                    'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_NAME',
                    'SEO_ADDITIONAL' => '--Дополнительно',
                    'TAGS',
                ],
            ]
        );

        //форма элемента articles
        $this->getHelper()->AdminIblock()->buildElementForm(
            $articlesIblock,
            [
                'Статья'   => [
                    'ID',
                    'DATE_CREATE',
                    'TIMESTAMP_X',
                    'ACTIVE',
                    'ACTIVE_FROM',
                    'ACTIVE_TO',
                    'NAME',
                    'CODE',
                    'XML_ID',
                    'SORT',
                    'PROPERTY_PUBLICATION_TYPE',
                    'PROPERTY_TYPE',
                    'PROPERTY_PRODUCTS',
                    'PROPERTY_VIDEO',
                    'PROPERTY_MORE_PHOTO',
                    'PROPERTY_IN_LANDING', //Добавлено
                    'PROPERTY_OLD_URL', //Добавлено
                ],
                'Анонс'    => [
                    'PREVIEW_PICTURE',
                    'PREVIEW_TEXT',
                ],
                'Подробно' => [
                    'DETAIL_PICTURE',
                    'DETAIL_TEXT',
                ],
                'SEO'      => [
                    'IPROPERTY_TEMPLATES_ELEMENT_META_TITLE',
                    'IPROPERTY_TEMPLATES_ELEMENT_META_KEYWORDS',
                    'IPROPERTY_TEMPLATES_ELEMENT_META_DESCRIPTION',
                    'IPROPERTY_TEMPLATES_ELEMENT_PAGE_TITLE',
                    'IPROPERTY_TEMPLATES_ELEMENTS_PREVIEW_PICTURE',
                    'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_ALT',
                    'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_TITLE',
                    'IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_NAME',
                    'IPROPERTY_TEMPLATES_ELEMENTS_DETAIL_PICTURE',
                    'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_ALT',
                    'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_TITLE',
                    'IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_NAME',
                    'SEO_ADDITIONAL' => '--Дополнительно',
                    'TAGS',
                ],
                'Разделы'  => [
                    'SECTIONS',
                ],
            ]
        );
        $this->log()->info('Установка интерфейсов завершена');

        $this->log()->info('Установка баннеров для ленднга');
        $imgPreview = \CFile::MakeFileArray(Application::getDocumentRoot().'/upload/iblock/388/3881536abacca3613366788bd143a05e.jpg');
        $imgDetail = \CFile::MakeFileArray(Application::getDocumentRoot().'/upload/iblock/a5d/a5dcc77b8ddd111810925d2f4e480710.png');
        $imgBackground = \CFile::MakeFileArray(Application::getDocumentRoot().'/upload/iblock/a34/a349c9612026e7ebec38c4db4fd91c42.jpg');
        $imgTablet = \CFile::MakeFileArray(Application::getDocumentRoot().'/upload/iblock/3b5/3b54d37ee72036b6e74afedb69959713.jpg');
        if(!empty($imgPreview) && !empty($imgDetail) && !empty($imgTablet)) {
            $helper->Iblock()->addElementIfNotExists(
                $bannerIblock,
                [
                    'NAME'              => 'Поездка в Париж',
                    'CODE'              => 'parish',
                    'SORT'              => 10,
                    'XML_ID'            => '70905',
                    'IBLOCK_SECTION_ID' => 617,
                    'PREVIEW_PICTURE'   => $imgPreview,
                    'DETAIL_PICTURE'    => $imgDetail,
                ],
                [
                    'IN_LANDING' => 'Y',
                    'LINK'       => 'http://stage.4lapy.adv.ru/customer/shares/mealfeel-puteshestvie-v-parizh-na-dvoikh/',
                    'SECTION'    => 163,//привязка баннера к разделу
                    'BACKGROUND' => $imgBackground,
                    'IMG_TABLET' => $imgTablet,
                ]);
            $helper->Iblock()->addElementIfNotExists($bannerIblock,
                [
                    'NAME'              => 'Поездка в Париж2',
                    'CODE'              => 'parish2',
                    'SORT'              => 10,
                    'XML_ID'            => '70905-2',
                    'IBLOCK_SECTION_ID' => 617,
                    'PREVIEW_PICTURE'   => $imgPreview,
                    'DETAIL_PICTURE'    => $imgDetail,
                ],
                [
                    'IN_LANDING' => 'Y',
                    'LINK'       => 'http://stage.4lapy.adv.ru/customer/shares/mealfeel-puteshestvie-v-parizh-na-dvoikh/',
                    'SECTION'    => 163,//привязка баннера к разделу
                    'BACKGROUND' => $imgBackground,
                    'IMG_TABLET' => $imgTablet,
                ]);
            $this->log()->info('Установка баннеров для ленднга интерфейсов завершена');
        }
        else{
            $this->log()->info('Изображения не найдены');
        }
    }
}