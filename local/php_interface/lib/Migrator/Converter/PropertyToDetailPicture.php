<?php

namespace FourPaws\Migrator\Converter;

/**
 * Class File
 *
 * Специфичный для проекта конвертер
 * Преобразует свойство в DETAIL_PICTURE для изображения предложения
 *
 * @package FourPaws\Migrator\Converter
 */
class PropertyToDetailPicture extends File
{
    const PROPERTY_IMG_KEY = 'PROPERTY_IMG';

    /**
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function convert(array $data) : array
    {
        $fieldName = $this->getFieldName();
        
        if (!$data[self::PROPERTY_IMG_KEY]) {
            return $data;
        }

        $imgPath = $data[self::PROPERTY_IMG_KEY];

        if (is_array($imgPath)) {
            $imgPath = array_shift($imgPath);
        }

        $data[$fieldName] = $this->getPicture($imgPath);
        
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
        
        return $fileArray;
    }
}