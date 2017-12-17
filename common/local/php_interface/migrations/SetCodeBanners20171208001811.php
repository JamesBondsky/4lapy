<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class SetCodeBanners20171208001811 extends SprintMigrationBase
{
    protected static $bannersIblockID   = 8;
    
    protected static $bannersIblockCODE = 'banners';
    
    public function up()
    {
        $iblock = new \CIBlock();
        $iblock->Update(static::$bannersIblockID, ['CODE' => static::$bannersIblockCODE]);
    }
    
    public function down()
    {
        $iblock = new \CIBlock();
        $iblock->Update(static::$bannersIblockID, ['CODE' => '']);
    }
}
