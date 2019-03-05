<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\CatalogBundle\Service\BrandService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\IblockProperty;
use FourPaws\App\Application;
use FourPaws\Catalog\Exception\BrandNotFoundException;

class AddOptBonusPropertyForBrand20190304123630 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    /**
     * @var string
     */
    protected $description = "Добавляет свойство \"Бонус 5% для заводчиков\" для брендов";

    /**
     * Бренды, которым нужно установить значение "Да"
     */
    protected $brands = [
        'Murmix',
        'UnoCat',
        'yummy',
        'chewell',
        'nagrada',
        'domosedy',
        'sanpet',
        'long-feng',
        'sivoket1',
        'clean-cat',
        'georplast',
        'mp-bergamo',
        'petmax',
        'pet-hobby',
        'rungo',
        'rurri',
    ];

    /**
     * @return bool
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws BrandNotFoundException
     */
    public function up(){
        $helper = new HelperManager();
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::BRANDS);

        /** @var BrandService $brandService */
        $brandService = Application::getInstance()->getContainer()->get('brand.service');

        $propId = $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME'               => 'Бонус 5% для заводчиков',
            'ACTIVE'             => 'Y',
            'SORT'               => '100',
            'CODE'               => IblockProperty::BRAND_BONUS_OPT,
            'DEFAULT_VALUE'      => '',
            'PROPERTY_TYPE'      => 'N:WebArch\BitrixIblockPropertyType\YesNoType',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '',
            'MULTIPLE_CNT'       => '5',
            'TMP_ID'             => null,
            'LINK_IBLOCK_ID'     => '0',
            'WITH_DESCRIPTION'   => 'N',
            'SEARCHABLE'         => 'N',
            'FILTRABLE'          => 'N',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '1',
            'USER_TYPE'          => null,
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
        ]);

        foreach($this->brands as $brandCode){
            try {
                $brand = $brandService->getByCode($brandCode);
                \CIBlockElement::SetPropertyValuesEx($brand->getId(), $iblockId, [$propId => true]);
            }
            catch(BrandNotFoundException $e) {
                $this->log()->error(sprintf($e->getMessage()));
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    public function down(){
        $helper = new HelperManager();
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::BRANDS);
        $helper->Iblock()->deletePropertyIfExists($iblockId, IblockProperty::BRAND_BONUS_OPT);

        return true;
    }

}
