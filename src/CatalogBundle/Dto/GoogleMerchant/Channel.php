<?php

namespace FourPaws\CatalogBundle\Dto\GoogleMerchant;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Channel
 *
 * @package FourPaws\CatalogBundle\Dto\GoogleMerchant
 */
class Channel
{
    /**
     * Имя компании
     *
     * @Required()
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $title = '4lapy - Online Store';

    /**
     * Урл сайта
     *
     * @Required()
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $link = 'https://4lapy.ru';

    /**
     * Оффсет
     *
     * @Serializer\SkipWhenEmpty()
     * @Serializer\Type("int")
     *
     * @var int
     */
    protected $offset;

    /**
     * Торговые предложения
     *
     * @Serializer\XmlList(inline=false, entry="offer")
     * @Serializer\Type("ArrayCollection<FourPaws\CatalogBundle\Dto\GoogleMerchant\Item>")
     *
     * @var Item[]|Collection
     */
    protected $items;

    /**
     * @return ArrayCollection|Item[]
     */
    public function getItems()
    {
        return $this->items ?? new ArrayCollection();
    }

    /**
     * @param Collection|Item[] $items
     *
     * @return Channel
     */
    public function setItems($items): Channel
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return int
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     *
     * @return Channel
     */
    public function setOffset(?int $offset): Channel
    {
        $this->offset = $offset;

        return $this;
    }
}
