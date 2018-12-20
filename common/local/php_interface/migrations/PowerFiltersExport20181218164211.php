<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PowerFiltersExport20181218164211 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Экспорт данных по мощностям фильтров в файл';

    protected $filePath = '/upload/powerExport/';
    protected $fileName = 'powerExport.json';

    public function up()
    {
        $helper = new HelperManager();


        $dir = $_SERVER['DOCUMENT_ROOT'] . $this->filePath;
        $file = $dir . $this->fileName;

        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($dir)) {
            $fileSystem->mkdir($dir);
        }

        $fileSystem->touch($file);

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'PROPERTY_POWER_MAX'
        ];

        $arFilter = [
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS),
            'ACTIVE' => 'Y',
            '!PROPERTY_POWER_MAX' => false,
            [
                'LOGIC' => 'OR',
                [
                    'SECTION_CODE' => 'vnutrennie-filtry-ryby'
                ],
                [
                    'SECTION_CODE' => 'vneshnie-filtry-ryby'
                ]
            ],
        ];

        $result = [];

        $dbItems = \CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while ($arItem = $dbItems->Fetch()) {
            $result[] = [
                'id' => $arItem['ID'],
                'power' => $arItem['PROPERTY_POWER_MAX_VALUE']
            ];
        }

        $jsonContent = $serializer->serialize($result, 'json');
        $fileSystem->dumpFile($file, $jsonContent);


        return true;
    }

    public function down()
    {
        $helper = new HelperManager();

        return true;

    }
}