<?php

namespace FourPaws\Test\Tests\Catalog;

use FourPaws\Catalog\Model\Filter\PriceFilter;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\PriceQuery;
use FourPaws\Test\Tests\TestBase;

class PriceTest extends TestBase
{
//    /**
//     * @throws \PHPUnit_Framework_Exception
//     */
//    public function testGetAllPrices()
//    {
//        //TODO КОгда у всех офферов будут цены, убрать явное прописывание ID тут
//        /** @var Offer $offer */
//        $offer = (new OfferQuery())->withFilter(['=ID' => 30083])->withOrder(['RAND' => 'ASC'])->withNav(
//            ['nTopCount' => 1]
//        )->exec()->current();
//
//        $this->assertInstanceOf(Offer::class, $offer);
//
//        (new PriceQuery())->getAllPrices($offer->getId());
//
//    }
//
//    public function testGetPriceRange()
//    {
//        $priceFilter = new PriceFilter();
//
//        $minValue = $priceFilter->getMinValue();
//        $this->assertGreaterThan(0, $minValue);
//        $this->assertGreaterThanOrEqual($minValue, $priceFilter->getMaxValue());
//    }
}
