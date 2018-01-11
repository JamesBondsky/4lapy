<?php

namespace FourPaws\BitrixOrm\Model\Interfaces;

interface ItemInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param int $id
     *
     * @return static
     */
    public function withId(int $id): ItemInterface;

    /**
     * @return string
     */
    public function getXmlId(): string;

    /**
     * @param string $xmlId
     *
     * @return static
     */
    public function withXmlId(string $xmlId): ItemInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     *
     * @return static
     */
    public function withName(string $name): ItemInterface;

    /**
     * @return int
     */
    public function getSort(): int;

    /**
     * @param int $sort
     *
     * @return static
     */
    public function withSort(int $sort): ItemInterface;
}
