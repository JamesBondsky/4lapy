<?php
/**
 * Created by PhpStorm.
 * Date: 02.04.2018
 * Time: 15:22
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SapBundle\Dto\In\Shares;

use Bitrix\Iblock\ElementTable;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/**
 * Interface BonusBuyGroupInterface
 * @package FourPaws\SapBundle\Dto\In\Shares
 */
abstract class BonusBuyGroupBase
{
    /**
     * Возвращает массив XML_ID, пришедших в импорте
     *
     * @return ArrayCollection
     */
    abstract public function getProductXmlIds(): ArrayCollection;

    /**
     * Возвращает массив ID предложений, существующих на сайте
     *
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\ArgumentException
     *
     * @return ArrayCollection
     */
    public function getProductIds(): ArrayCollection
    {
        if ($xmlIds = $this->getProductXmlIds()->toArray()) {
            $res = ElementTable::getList([
                'select' => ['ID'],
                'filter' => [
                    '=XML_ID' => $xmlIds,
                    '=IBLOCK.CODE' => IblockCode::OFFERS,
                    '=IBLOCK.TYPE.ID' => IblockType::CATALOG,
                ],
            ]);
            $result = [];
            while ($elem = $res->fetch()) {
                $result[] = (int)$elem['ID'];
            }
            $result = array_filter($result);
            $result = new ArrayCollection($result);
        }
        return $result ?? new ArrayCollection();
    }
}