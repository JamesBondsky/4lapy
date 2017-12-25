<?php
/**
 * @var Product $product
 * @var CMain   $APPLICATION
 */

use FourPaws\Catalog\Model\Product;

global $APPLICATION;

$APPLICATION->IncludeComponent('fourpaws:catalog.element.snippet', '', ['PRODUCT' => $product]);
