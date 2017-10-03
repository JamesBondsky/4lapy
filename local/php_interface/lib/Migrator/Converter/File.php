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
class File extends AbstractConverter
{
    private $toProperty = false;
    
    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function convert(array $data) : array
    {
        $result    = [];
        $isArray   = true;
        $fieldName = $this->getFieldName();
        
        if (!$data[$fieldName]) {
            return $data;
        }
        
        if (!is_array($data[$fieldName])) {
            $isArray          = false;
            $data[$fieldName] = [$data[$fieldName]];
        }
        
        foreach ($data[$fieldName] as $value) {
            $result[] = $this->getPicture($value);
        }
        
        $data[$fieldName] = $isArray ? $result : array_shift($result);
        
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
        
        $fileArray = \CFile::MakeFileArray($path);
        
        return $this->toProperty ? ['VALUE' => $fileArray] : $fileArray;
    }
    
    /**
     * Для свойств элемента иблока другой массив (Bitrix. No way.)
     */
    public function setToProperty()
    {
        $this->toProperty = true;
    }
}