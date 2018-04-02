<?php
/**
 * Created by PhpStorm.
 * Date: 02.04.2018
 * Time: 15:22
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SapBundle\Dto\In\Shares;


/**
 * Interface BonusBuyGroupInterface
 * @package FourPaws\SapBundle\Dto\In\Shares
 */
interface BonusBuyGroupInterface
{
    /**
     * Возвращает массив XML_ID, пришедших в импорте
     *
     * @return array
     */
    public function getProductXmlIds(): array;

    /**
     * Возвращает массив ID предложений, существующих на сайте
     *
     * @return array
     */
    public function getProductIds(): array;
}