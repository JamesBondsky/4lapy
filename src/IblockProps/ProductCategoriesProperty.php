<?php

namespace FourPaws\IblockProps;

use WebArch\BitrixIblockPropertyType\Abstraction\IblockPropertyTypeBase;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\FileInput;

class ProductCategoriesProperty
{

    public function init()
    {
        AddEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            [$this, 'getUserTypeDescription']
        );
    }

    function GetUserTypeDescription()
    {
        return [
            'DESCRIPTION' => 'Товарные категории',
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'ProductCategoriesProperty',
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
        ];
    }

    public static function ConvertToDB($arProperty, $value)
    {
        $result = json_decode($value['VALUE'], true);
        $delFiles = $_REQUEST['product_categories_images_del'];
        foreach ($delFiles as $delID => $val) {
            foreach ($result as $key => $res) {
                if ($res['move_id'] == $delID) {
                    $fileID = $result[$key]['image_id'];
                    \CFile::Delete($fileID);
                    unset($result[$key]['image_id']);
                    break;
                }
            }
        }

        $files = $_REQUEST['product_categories_images'];
        $descriptions = $_REQUEST['product_categories_images_descr'];
        foreach ($files as $setID => $file) {
            if (!empty($file['tmp_name']) && !(empty($file['name']))) {
                $fileArray = \CFile::MakeFileArray('/upload/tmp' . $file['tmp_name']);
                $fileArray['name'] = $file['name'];
                $fileArray['description'] = $descriptions[$setID];
                foreach ($result as $key => $res) {
                    if ($res['move_id'] == $setID) {
                        $result[$key]['image_id'] = strval(\CFile::SaveFile($fileArray, 'iblock'));
                        break;
                    }
                }
            } elseif ((int)$file == 0) {
                $fileArray = \CFile::MakeFileArray($file);
                $fileArray['description'] = $descriptions[$setID];
                $result[$key]['image_id'] = strval(\CFile::SaveFile($fileArray, 'iblock'));
            }
        }

        if (!empty($descriptions)) {
            foreach ($descriptions as $setID => $descr) {
                if (!empty($descr)) {
                    foreach ($result as $key => $res) {
                        if ($res['move_id'] == $setID) {
                            \CFile::UpdateDesc($result[$key]['image_id'], $descr);
                            break;
                        }
                    }
                }
            }
        }

        foreach ($result as $key => &$res) {
            $res['move_id'] = $key;
            if ($key < 4) {
                $res['row'] = 1;
            } elseif ($key < 7) {
                $res['row'] = 2;
            } else {
                $res['row'] = 3;
            }
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function GetPropertyFieldHtml(array $property, array $value, array $control)
    {
        Asset::getInstance()->addJs('/local/include/js/Sortable/Sortable.js');

        $template = file_get_contents(__DIR__ . '/templates/ProductCategoriesProperty.html');

        if ($value['VALUE'] != null) {
            $initValues = json_decode($value['VALUE'], true);
        } else {
            for ($i = 0; $i < 11; $i++) {
                $initValues[$i] = [
                    'row' => '',
                    'title' => '',
                    'subtitle' => '',
                    'filters' => ''
                ];
            }
        }

        for ($i = 0; $i < 11; $i++) {
            $arFiles[$i] = \Bitrix\Main\UI\FileInput::createInstance([
                'name' => 'product_categories_images' . '[' . $i . ']',
                'id' => 'product_categories_images' . '[' . $i . ']' . '_' . $initValues[$i]['image_id'],
                'description' => true,
                'allowUpload' => 'F',
                'allowUploadExt' => 'jpg, gif, bmp, png, jpeg',
                'maxCount' => 1,
                'upload' => true,
                'medialib' => true,
                'fileDialog' => true,
                'cloud' => true
            ])->show($initValues[$i]['image_id']);
        }


        $template = str_replace(
            [
                '#CONTROL_NAME#',
                '#CONTROL_NAME_VALUE#',
                '#FILE_0#',
                '#FILE_1#',
                '#FILE_2#',
                '#FILE_3#',
                '#FILE_4#',
                '#FILE_5#',
                '#FILE_6#',
                '#FILE_7#',
                '#FILE_8#',
                '#FILE_9#',
                '#FILE_10#'
            ],
            [
                $control['VALUE'],
                json_encode($initValues, JSON_UNESCAPED_UNICODE),
                $arFiles[0],
                $arFiles[1],
                $arFiles[2],
                $arFiles[3],
                $arFiles[4],
                $arFiles[5],
                $arFiles[6],
                $arFiles[7],
                $arFiles[8],
                $arFiles[9],
                $arFiles[10]
            ], $template);

        return $template;
    }
}