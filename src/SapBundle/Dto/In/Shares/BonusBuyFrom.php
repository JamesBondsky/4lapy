<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\Shares;

use Bitrix\Iblock\ElementTable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class BonusBuyFrom
 *
 * @package FourPaws\SapBundle\Dto\In\Shares
 */
class BonusBuyFrom implements BonusBuyGroupInterface
{
    /**
     * Содержит вид предпосылки. Тип поля – единственный выбор из значений:
     *
     * - MAT (материал) – товар;
     * - MGP (группировка материала) – группа товаров. Для определения количества товаров в группе должна быть создана
     *      позиция предпосылки с единственным заполненным параметром MAT_QUAN.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("PRQ_TYPE")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $type = '';

    /**
     * Содержит номер группы товаров. Заполняется для вида предпосылки MGP.
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("GRPG_NR")
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $groupId = '';

    /**
     * Содержит индикатор «Любая комбинация».
     *
     * @Serializer\XmlAttribute()
     * @Serializer\SerializedName("SUM_FLAG")
     * @Serializer\Type("sap_bool")
     *
     * @var bool
     */
    protected $anyCombination = false;

    /**
     * Группа данных о позиции предпосылки акции
     *
     * @Serializer\XmlList(inline=true, entry="PURCHASE_ITEM")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Shares\BonusBuyFromItem>")
     *
     * @var BonusBuyFromItem[]|Collection
     */
    protected $bonusBuyFromItems;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return BonusBuyFrom
     */
    public function setType(string $type): BonusBuyFrom
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroupId(): string
    {
        return $this->groupId;
    }

    /**
     * @param string $groupId
     *
     * @return BonusBuyFrom
     */
    public function setGroupId(string $groupId): BonusBuyFrom
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAnyCombination(): bool
    {
        return $this->anyCombination;
    }

    /**
     * @param bool $anyCombination
     *
     * @return BonusBuyFrom
     */
    public function setAnyCombination(bool $anyCombination): BonusBuyFrom
    {
        $this->anyCombination = $anyCombination;

        return $this;
    }

    /**
     * @return BonusBuyFromItem[]|Collection
     */
    public function getBonusBuyFromItems(): Collection
    {
        return $this->bonusBuyFromItems->filter(function ($item) {
            /**
             * @var $item BonusBuyFromItem
             */
            return $item->getOfferId() > 0;
        });
    }

    /**
     * Для определения количества товаров в группе должна быть создана позиция предпосылки с единственным заполненным
     * параметром MAT_QUAN.
     *
     * @return int
     */
    public function getGroupQuantity(): int
    {
        $item = $this->bonusBuyFromItems->filter(function ($item) {
            /**
             * @var $item BonusBuyFromItem
             */
            return empty($item->getOfferId());
        })->first();

        /**
         * @var $item BonusBuyFromItem
         */
        return $item ? $item->getQuantity() : 0;
    }

    /**
     * @param BonusBuyFromItem[]|Collection $bonusBuyFromItems
     *
     * @return BonusBuyFrom
     */
    public function setBonusBuyFromItems($bonusBuyFromItems): BonusBuyFrom
    {
        $this->bonusBuyFromItems = $bonusBuyFromItems;

        return $this;
    }


    /**
     * Возвращает массив XML_ID, пришедших в импорте
     *
     * @return ArrayCollection
     */
    public function getProductXmlIds(): ArrayCollection
    {
        /**
         * больше одного, так как в первом содержится количество элементов
         */
        if (!empty($this->bonusBuyFromItems) && $this->bonusBuyFromItems->count() > 1) {
            /** @var ArrayCollection $result */
            $result = $this->bonusBuyFromItems->map(function (BonusBuyFromItem $item) {
                return $item->getOfferId();
            });

            $result = $result->filter(
                function ($e) {
                    return (bool)$e;
                }
            );
        }
        return $result ?? new ArrayCollection();
    }

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
                $result[] = $elem['ID'];
            }
            $result = array_filter($result);
            $result = new ArrayCollection($result);
        }
        return $result ?? new ArrayCollection();
    }
}
