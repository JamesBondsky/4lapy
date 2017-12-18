<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Helpers;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\DB\Result;
use Bitrix\Main\Loader;

/**
 * Class HighloadHelper
 *
 * @package FourPaws\Helpers
 */
class HighloadHelper
{
    /**
     * @param string $name
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @return int
     */
    public static function getIdByName(string $name) : int
    {
        $params = [
            'select' => ['ID'],
            'filter' => ['NAME' => $name],
            'cache'  => ['ttl' => 360000],
        ];
        
        return (int)static::getHighloadTableRes($params)->fetch()['ID'];
    }
    
    /**
     * @param array $params
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @return \Bitrix\Main\DB\Result
     */
    public static function getHighloadTableRes(array $params) : Result
    {
        Loader::includeModule('highloadblock');
        
        return HighloadBlockTable::getList($params);
    }
    
    /**
     * @param string $name
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @return int
     */
    public static function getIdByTableName(string $name) : int
    {
        $params = [
            'select' => ['ID'],
            'filter' => ['TABLE_NAME' => $name],
            'cache'  => ['ttl' => 360000],
        ];
        
        return (int)static::getHighloadTableRes($params)->fetch()['ID'];
    }
}
