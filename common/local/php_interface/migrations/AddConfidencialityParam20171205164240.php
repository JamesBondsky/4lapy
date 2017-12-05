<?php

namespace Sprint\Migration;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

class AddConfidencialityParam20171205164240 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    
    protected static $siteID                        = 's1';
    protected static $codeConfidentialityDate       = 'confidentiality_date';
    protected static $startConfidentialityDateValue = '28 июля 2017';
    
    public function up()
    {
        //$helper = new HelperManager();
        
        Loader::includeModule('asd.tplvars');
        tplvar_set(static::$codeConfidentialityDate, static::$startConfidentialityDateValue, static::$siteID);
        
        return true;
        
    }
    
    public function down()
    {
        //$helper = new HelperManager();
        
        Option::delete('tpl_vars',
                       [
                           'name'    => static::$codeConfidentialityDate,
                           'site_id' => static::$siteID,
                       ]);
        
        return true;
        
    }
    
}
