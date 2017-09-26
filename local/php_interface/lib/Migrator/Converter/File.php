<?php

namespace FourPaws\Migrator\Converter;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Highloadblock\DataManager;

/**
 * Class File
 *
 * Выдаёт массив для привязки картинки
 *
 * @package FourPaws\Migrator\Converter
 */
final class File extends AbstractConverter
{
    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function convert(array $data) : array
    {
        $fieldName = $this->getFieldName();
        
        if ($data[$fieldName]) {
            $data[$fieldName] = $this->getPicture($data[$fieldName]);
        }
        
        return $data;
    }
    
    /**
     * @param string $path
     *
     * @return array
     */
    public function getPicture(string $path) : array
    {
        /**
         * Костыль - на old4lapy.e.adv.ru не загружены файлы
         */
        if (strpos($path, 'old4lapy.e.adv.ru') !== false) {
            $path = str_replace('old4lapy.e.adv.ru', '4lapy.ru', $path);
        }

        return \CFile::MakeFileArray($path);
    }
}