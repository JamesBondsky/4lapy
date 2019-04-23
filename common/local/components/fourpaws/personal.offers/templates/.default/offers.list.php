<?php

use Doctrine\Common\Collections\ArrayCollection;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var ArrayCollection $coupons
 * @var ArrayCollection $offers
 */
?>
<?
if (!$coupons->isEmpty()) {
	?>
	<h2 class="b-title b-personal-offers__title">Персональные предложения</h2>
	<div class="b-personal-offers__list">
		<?
		/** @var array $coupon */
        foreach ($coupons as $coupon) {
        	/** @var ArrayCollection $offers */
        	$offer = $offers->filter(function($offer) use($coupon) {
        		return $offer['ID'] === $coupon['UF_OFFER'];
	        })->first();

			?>
		    <div class="b-personal-offers-item__wrap">
		        <div class="b-personal-offers-item">
		            <div class="b-personal-offers-item__offers">
		                <div class="b-personal-offers-item__percent">
		                    <div class="percent-title"><?= $offer['PROPERTY_DISCOUNT_VALUE'] ?>%</div>
		                    <div class="percent-descr"><?= $offer['~PREVIEW_TEXT'] ?><?= $offer['DATE_ACTIVE_TO'] ? ' до&nbsp;<nobr>' . $offer['DATE_ACTIVE_TO'] . '</nobr>' : '' ?></div>
		                </div>
		            </div>
		            <div class="b-personal-offers-item__data-wrap">
		                <div class="b-personal-offers-item__data">
		                    <div class="b-personal-offers-item__digital-code" data-container-number-coupon="true">
		                        <span class="text" data-number-coupon="true"><?= $coupon['UF_PROMO_CODE'] ?></span>
		                        <a href="javascript:void(0);" class="link" data-link-copy-number-coupon="true">Скопировать</a>
		                    </div>
		                    <div class="b-personal-offers-item__barcode-img">
                                <? $barcodeGenerator = new \Picqer\Barcode\BarcodeGeneratorPNG()?>
			                    <img src="data:image/png;base64,<?=base64_encode($barcodeGenerator->getBarcode($coupon['UF_PROMO_CODE'], \Picqer\Barcode\BarcodeGenerator::TYPE_CODE_128, 2.132310384278889, 127))?>" alt="" />
		                        <?/*?><img src="/static/build/images/content/barcode-kopilka.png" alt="" /><?*/?>
		                    </div>
		                    <a href="javascript:void(0);"
		                       class="b-personal-offers-item__barcode-btn js-open-popup"
		                       data-popup-id="send-email-personal-offers"
		                       data-id-coupon-personal-offers="<?= $coupon['UF_PROMO_CODE'] ?>">Отправить мне на Email</a>
		                </div>
		            </div>
		        </div>
		    </div>
		    <?/* // Персональное предложение с подарком ?><div class="b-personal-offers-item__wrap">
		        <div class="b-personal-offers-item">
		            <div class="b-personal-offers-item__offers">
		                <div class="b-personal-offers-item__product">
		                    <div class="product-img">
		                        <img src="/upload/coupon/dog.png" alt="" />
		                    </div>
		                    <div class="product-title">
		                        <span><b>Petmax</b> Игрушка для собак Такса, латекс, 19&nbsp;см</span>
		                    </div>
		                    <div class="product-descr">
		                        <span>В&nbsp;подарок при следущей покупке до&nbsp;<nobr>30.11.2019</nobr></span>
		                    </div>
		                </div>
		            </div>
		            <div class="b-personal-offers-item__data-wrap">
		                <div class="b-personal-offers-item__data">
		                    <div class="b-personal-offers-item__digital-code" data-container-number-coupon="true">
		                        <span class="text" data-number-coupon="true">GJQ123DFE4567</span>
		                        <a href="javascript:void(0);" class="link" data-link-copy-number-coupon="true">Скопировать</a>
		                    </div>
		                    <div class="b-personal-offers-item__barcode-img">
		                        <img src="/static/build/images/content/barcode-kopilka.png" alt="" />
		                    </div>
		                    <a href="javascript:void(0);"
		                       class="b-personal-offers-item__barcode-btn js-open-popup"
		                       data-popup-id="send-email-personal-offers"
		                       data-id-coupon-personal-offers="GJQ123DFE4567">Отправить мне на Email</a>
		                </div>
		            </div>
		        </div>
		    </div><?*/?>
		<?
		}
		?>
	</div>
	<?
}