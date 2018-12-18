<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class PowerFiltersImport20181218164230 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = "Импорт данных по мощностям фильтров в бд";

    protected $filePath = '/upload/powerExport/';
    protected $fileName = 'powerExport.json';

    public function up()
    {
        $dir = $_SERVER['DOCUMENT_ROOT'] . $this->filePath;
        $file = $dir . $this->fileName;

        $result = json_decode(file_get_contents($file), true);

        $iblockID = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);

        foreach ($result as $item) {
            if (isset($item['id']) && isset($item['power']) && $item['id'] > 0 && $item['power'] > 0) {
                \CIBlockElement::SetPropertyValuesEx(
                    $item['id'],
                    $iblockID,
                    [
                        'POWER_MAX' => $item['power']
                    ]
                );
            }
        }

        return true;

    }

    public function down()
    {
        $helper = new HelperManager();

        return true;
    }

}
