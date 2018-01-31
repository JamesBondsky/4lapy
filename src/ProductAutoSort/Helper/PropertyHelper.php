<?php

namespace FourPaws\ProductAutoSort\Helper;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\PropertyTable;
use CUserTypeEntity;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use RuntimeException;

class PropertyHelper
{
    /**
     * @var int
     */
    protected $ufPropCondId;

    /**
     * @var array Свойства каталога.
     */
    private $catalogProperties;

    /**
     * @throws RuntimeException
     * @return int
     */
    public function getUfPropCondIdForProducts()
    {
        if (null === $this->ufPropCondId) {
            $fieldName = 'UF_PROP_COND';
            $ufPropCond = CUserTypeEntity::GetList(
                [],
                [
                    'ENTITY_ID'  => sprintf(
                        'IBLOCK_%d_SECTION',
                        IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS)
                    ),
                    'FIELD_NAME' => $fieldName,
                ]
            )
                                         ->Fetch();

            if (false == $ufPropCond || !isset($ufPropCond['ID'])) {
                throw new RuntimeException('Не найдено пользовательское поле ' . $fieldName);
            }

            $this->ufPropCondId = (int)$ufPropCond['ID'];
        }

        return $this->ufPropCondId;
    }

    /**
     * @param $propertyId
     *
     * @return bool
     */
    public function isProductProperty(int $propertyId)
    {
        $catalogProps = $this->getCatalogProperties();

        return
            isset($catalogProps[$propertyId], $catalogProps[$propertyId]['IBLOCK_ID'])
            && $catalogProps[$propertyId]['IBLOCK_ID'] == IblockUtils::getIblockId(
                IblockType::CATALOG,
                IblockCode::PRODUCTS
            );
    }

    /**
     * @param $propertyId
     *
     * @return bool
     */
    public function isOfferProperty(int $propertyId)
    {
        $catalogProps = $this->getCatalogProperties();

        return
            isset($catalogProps[$propertyId], $catalogProps[$propertyId]['IBLOCK_ID'])
            && $catalogProps[$propertyId]['IBLOCK_ID'] == IblockUtils::getIblockId(
                IblockType::CATALOG,
                IblockCode::OFFERS
            );
    }

    /**
     * @param $propertyId
     *
     * @return bool
     */
    public function isMultipleProperty(int $propertyId)
    {
        $catalogProps = $this->getCatalogProperties();

        return
            isset($catalogProps[$propertyId], $catalogProps[$propertyId]['MULTIPLE'])
            && $catalogProps[$propertyId]['MULTIPLE'] === 'Y';
    }

    /**
     * @return array propertyId => propertyFields
     */
    private function getCatalogProperties()
    {
        if (null === $this->catalogProperties) {
            $dbPropertyList = PropertyTable::query()->setSelect(['*'])
                                           ->setFilter(
                                               [
                                                   '=IBLOCK_ID' => [
                                                       IblockUtils::getIblockId(
                                                           IblockType::CATALOG,
                                                           IblockCode::PRODUCTS
                                                       ),
                                                       IblockUtils::getIblockId(
                                                           IblockType::CATALOG,
                                                           IblockCode::OFFERS
                                                       ),
                                                   ],
                                               ]
                                           )
                                           ->exec();
            $this->catalogProperties = [];
            while ($property = $dbPropertyList->fetch()) {
                $this->catalogProperties[(int)$property['ID']] = $property;
            }
        }

        return $this->catalogProperties;
    }
}
