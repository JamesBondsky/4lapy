<?php

namespace FourPaws\CatalogBundle\Dto;


interface YmlOfferParameterInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     *
     * @return YmlOfferParameterInterface
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getValue(): string;

    /**
     * @param string $value
     *
     * @return YmlOfferParameterInterface
     */
    public function setValue(string $value);
}
