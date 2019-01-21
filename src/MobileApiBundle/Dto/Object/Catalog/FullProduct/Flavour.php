<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class Flavour
 *
 * @package FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct
 *
 * ОбъектКаталога.ПолныйТовар.Вкус
 */
class Flavour
{
    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("offerId")
     * @var int
     */
    protected $offerId;

    /**
     * @var string
     */
    protected $title;


    /**
     * @return int
     */
    public function getOfferId(): int
    {
        return $this->offerId;
    }

    /**
     * @param int $offerId
     * @return Flavour
     */
    public function setOfferId(int $offerId): Flavour
    {
        $this->offerId = $offerId;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Flavour
     */
    public function setTitle(string $title): Flavour
    {
        $this->title = $title;
        return $this;
    }
}
