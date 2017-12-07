<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

class SetCodeBanners20171208001811 extends SprintMigrationBase
{
    protected static $bannersIblockID   = 8;
    
    protected static $bannersIblockCODE = 'banners';
    
    public function up()
    {
        $helper = new HelperManager();
        $helper->Iblock()->updateIblockFields(self::$bannersIblockID, ['CODE' => self::$bannersIblockCODE]);
    }
    
    public function down()
    {
        $helper = new HelperManager();
        $helper->Iblock()->updateIblockFields(self::$bannersIblockID, ['CODE' => '']);
    }
}
