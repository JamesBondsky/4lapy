<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;

/**
 * Class SetCodeBanners20171208001811
 *
 * @package Sprint\Migration
 */
class SetCodeBanners20171208001811 extends SprintMigrationBase
{
    protected static $bannersIblockID   = 8;
    
    protected static $bannersIblockCODE = 'banners';
    
    /**
     * @return bool|void
     */
    public function up()
    {
        $iblock = new \CIBlock();
        $iblock->Update(static::$bannersIblockID, ['CODE' => static::$bannersIblockCODE]);
    }
    
    /**
     * @return bool|void
     */
    public function down()
    {
        $iblock = new \CIBlock();
        $iblock->Update(static::$bannersIblockID, ['CODE' => '']);
    }
}
