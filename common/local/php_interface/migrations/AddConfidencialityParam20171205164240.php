<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

/**
 * Class AddConfidencialityParam20171205164240
 *
 * @package Sprint\Migration
 */
class AddConfidencialityParam20171205164240 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected static $siteID                        = 's1';
    
    protected static $codeConfidentialityDate       = 'confidentiality_date';
    
    protected static $startConfidentialityDateValue = '28 июля 2017';
    
    protected $description                   = 'Добавление опции для конфиденциальности с установка занчения';
    
    /**
     * @throws \Bitrix\Main\LoaderException
     * @return bool
     */
    public function up() : bool
    {
        Loader::includeModule('asd.tplvars');
        tplvar_set(static::$codeConfidentialityDate, static::$startConfidentialityDateValue, static::$siteID);
        
        return true;
    }
    
    /**
     * @throws \Bitrix\Main\ArgumentNullException
     * @return bool
     */
    public function down() : bool
    {
        Option::delete(
            'tpl_vars',
            [
                'name'    => static::$codeConfidentialityDate,
                'site_id' => static::$siteID,
            ]
        );
        
        return true;
    }
}
