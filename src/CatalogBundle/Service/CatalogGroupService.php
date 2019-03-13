<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 13.03.2019
 * Time: 13:38
 */

namespace FourPaws\CatalogBundle\Service;

use Bitrix\Main\Entity\Query;

/**
 * Class CatalogGroupService
 *
 * @package FourPaws\CatalogBundle
 */
class CatalogGroupService
{
    public $groups;
    protected $inited = false;

    /**
     * @param string $regionCode
     * @return int|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getCatalogGroupIdByRegion(string $regionCode): ?int
    {
        if(!$this->inited){
            $this->init();
        }

        return $this->groups[$regionCode];
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function init()
    {
        $result = (new Query('Bitrix\Catalog\GroupTable'))
            ->setSelect(['ID', 'XML_ID'])
            ->setCacheTtl(31536000)
            ->exec();

        while($row = $result->fetch()){
            $this->groups[$row['XML_ID']] = (int)$row['ID'];
        }

        $this->inited = true;
    }
}