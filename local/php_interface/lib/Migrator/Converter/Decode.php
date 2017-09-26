<?php

namespace FourPaws\Migrator\Converter;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Highloadblock\DataManager;

/**
 * Class Encode
 *
 * Применяет htmlspecialchars_decode к полю
 *
 * @package FourPaws\Migrator\Converter
 */
final class Decode extends AbstractConverter
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
        
        if (!$data[$fieldName]) {
            return $data;
        }

        $data[$fieldName] = htmlspecialchars_decode($data[$fieldName]);
        
        return $data;
    }
}