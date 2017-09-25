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
    private $host = '';
    
    /**
     * @return string
     */
    public function getHost() : string
    {
        return $this->host;
    }
    
    /**
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }
    
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
            $data[$fieldName] = trim(($this->getHost() ?: ''), ' /') . $this->getPicture($data[$fieldName]);
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
        return \CFile::MakeFileArray($path);
    }
}