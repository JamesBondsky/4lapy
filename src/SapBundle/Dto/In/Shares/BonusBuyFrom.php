<?php

namespace FourPaws\SapBundle\Dto\In\Shares;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;


/**
 * Class BonusBuyFrom
 *
 * @package FourPaws\SapBundle\Dto\In\Shares
 */
class BonusBuyFrom
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
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Shares\BonusBuyFromItem>")
     * @Serializer\SerializedName("PURCHASE_ITEM")
     *
     * @var Collection|BonusBuyFromItem[]
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
     * @return Collection|BonusBuyFromItem[]
     */
    public function getBonusBuyFrom(): Collection
    {
        return $this->bonusBuyFromItems;
    }

    /**
     * @param Collection|BonusBuyFromItem[] $bonusBuyFromItems
     *
     * @return BonusBuyFrom
     */
    public function setBonusBuyFrom($bonusBuyFromItems): BonusBuyFrom
    {
        $this->bonusBuyFromItems = $bonusBuyFromItems;

        return $this;
    }
}
