<?
/**
 * @var CFashionProductSlider $component
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<section class="fashion-total-look-section">
    <div class="b-container">
        <? $n = 0; ?>
        <? foreach($arResult['ELEMENTS'] as $i => $element) { ?>
            <?  // Элементы расположены попарно
                $n++;
                if($n % 2 == 0) continue;
            ?>

            <div class="fashion-total-look-section__group <?=($n == 1) ? 'active' : '' ?>" style="display: <?=($n == 1) ? 'block' : 'none' ?>;" data-group-total-look-fashion="true">
                <div class="fashion-total-look" data-item-total-look-fashion="true">
                    <div class="fashion-total-look__slider" data-total-look-slider-fashion="true">
                        <? foreach ($element['PROPERTIES']['IMAGES']['VALUE'] as $imgId) { ?>
                            <div class="item-total-look-slider">
                                <img src="<?=$arResult['IMAGES'][$imgId]?>" alt="<?=$element['NAME']?>">
                            </div>
                        <? } ?>
                    </div>
                    <div class="fashion-total-look__list">
                        <div data-list-fashion-total-look="true" data-url="/ajax/catalog/product-info/">
                            <?php
                            foreach ($element['PROPERTIES']['PRODUCTS']['VALUE'] as $xmlId){
                                $product = $component->getProduct($xmlId);
                                $APPLICATION->IncludeComponent(
                                    'fourpaws:catalog.element.snippet',
                                    'fashion_slider',
                                    [
                                        'PRODUCT'               => $product,
                                        'GOOGLE_ECOMMERCE_TYPE' => 'Модная коллекция'
                                    ]
                                );
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <? $element2 = $arResult['ELEMENTS'][$i+1] ?>

                <div class="fashion-total-look" data-item-total-look-fashion="true">
                    <div class="fashion-total-look__slider" data-total-look-slider-fashion="true">
                        <? foreach ($element2['PROPERTIES']['IMAGES']['VALUE'] as $imgId) { ?>
                            <div class="item-total-look-slider">
                                <img src="<?=$arResult['IMAGES'][$imgId]?>" alt="<?=$element2['NAME']?>">
                            </div>
                        <? } ?>
                    </div>
                    <div class="fashion-total-look__list">
                        <div data-list-fashion-total-look="true" data-url="/ajax/catalog/product-info/">
                            <?php
                            foreach ($element2['PROPERTIES']['PRODUCTS']['VALUE'] as $xmlId){
                                $product = $component->getProduct($xmlId);
                                $APPLICATION->IncludeComponent(
                                    'fourpaws:catalog.element.snippet',
                                    'fashion_slider',
                                    [
                                        'PRODUCT'               => $product,
                                        'GOOGLE_ECOMMERCE_TYPE' => 'Модная коллекция'
                                    ]
                                );
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <? } ?>

        <div class="fashion-total-look-section__btn-wrap">
            <div class="fashion-total-look-section__btn" data-btn-next-look-fashion="true">Показать ещё</div>
        </div>
    </div>
</section>
