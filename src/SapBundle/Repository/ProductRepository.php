<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Repository;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Config\ConfigurationException;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrm\Model\IblockElement;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\SapBundle\Exception\NotFoundPropertyException;

class ProductRepository extends IblockElementRepository
{
    /**
     * @return int
     * @throws IblockNotFoundException
     */
    public function getIblockId(): int
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        return IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
    }

    /**
     * @return ArrayCollection|Collection|IblockElement[]
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     * @throws ConfigurationException
     * @throws IblockNotFoundException
     * @throws ObjectPropertyException
     * @throws SqlQueryException
     * @throws SystemException
     * @throws NotFoundPropertyException
     */
    public function getEmptyProducts()
    {
        $productsIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
        $offersIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);

        if (!$propId = PropertyTable::query()
            ->setSelect(['ID'])
            ->setFilter(['IBLOCK_ID' => $offersIblockId, 'CODE' => 'CML2_LINK'])
            ->exec()
            ->fetch()['ID']
        ) {
            throw new NotFoundPropertyException('Property CML2_LINK not found');
        }

        $productIds = array_column(
            Application::getConnection()->query(
                sprintf(
                    'SELECT e.ID AS ID
                  FROM b_iblock_element e
                  WHERE
                    e.IBLOCK_ID = %s AND
                    NOT EXISTS (SELECT * FROM b_iblock_element_prop_s%s p WHERE p.PROPERTY_%s = e.ID)
                ',
                    $productsIblockId,
                    $offersIblockId,
                    $propId
                )
            )->fetchAll(),
            'ID'
        );

        if (empty($productIds)) {
            $result = new ArrayCollection();
        } else {
            $result = $this->findBy(['ID' => $productIds]);
        }

        return $result;
    }

    /**
     * @return IblockElementQuery
     */
    protected function getQuery(): IblockElementQuery
    {
        return (new ProductQuery())
            ->withFilter([]);
    }
}
