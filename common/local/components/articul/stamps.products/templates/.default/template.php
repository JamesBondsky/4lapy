<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die; ?>
<? use FourPaws\Helpers\WordHelper; ?>

<? $itemNumber = 1; ?><? // для нумерации товаров?>
<? $imgFirst = true; ?><? // вспомогательная переменная для зеркального отображения следующего блока ?>

<? foreach ($arResult['sections'] as $sectionKey => $section): ?>
    <section class="toys-landing__section<?= ($sectionKey != 0) ? ' wo-sep' : '' ?>">
        <h2 class="toys-landing__section-title toys-landing__section-title--<?= ($sectionKey != 0) ? 'blue' : 'red' ?>">
            <span class="toys-landing__section-title-text"><?= $section['name'] ?></span>
        </h2>

        <div class="b-container">
            <? foreach ($arResult['products'][$section['id']] as $product): ?>
                <? $offer = $arResult['offers'][$product['offer_xml_id']]; ?>
                <div class="toys-landing-item toys-landing-item--<?= $itemNumber ?>">
                    <? for ($i = 0; $i < 2; $i++): ?>
                        <? if ($imgFirst xor ($i % 2)): ?>
                            <div class="toys-landing-item__splash">
                                <picture>
                                    <source srcset="<?= $arResult['images'][$product['preview_img']['desktop']] ?>" media="(min-width: 1024px)"/>
                                    <source srcset="<?= $arResult['images'][$product['preview_img']['mobile']] ?>" media="(max-width: 1023px)"/>

                                    <img src="/upload/toys-landing/1.png" alt="">
                                </picture>

                                <div class="toys-landing-item__splash-content">
                                    <h3 class="toys-landing-item__splash-title">
                                        <a href="<?= $offer['href'] ?>" target="_blank"><?= $product['name'] ?></a>
                                    </h3>
                                    <p class="toys-landing-item__splash-text"><?= $product['description'] ?></p>
                                </div>
                            </div>
                        <? else: ?>
                            <div class="toys-landing-item__product toys-landing-product">
                                <video class="toys-landing-product__video" poster="<?= $arResult['images'][$product['video']['preview']] ?>" controls>
                                    <? if ($product['video']['links']['mp4']): ?>
                                        <source src="<?= $product['video']['links']['mp4'] ?>">
                                    <? endif; ?>
                                    <? if ($product['video']['links']['ogv']): ?>
                                        <source src="<?= $product['video']['links']['ogv'] ?>" type="video/ogg">
                                    <? endif; ?>
                                    <? if ($product['video']['links']['webm']): ?>
                                        <source src="<?= $product['video']['links']['webm'] ?>" type="video/webm">
                                    <? endif; ?>
                                </video>

                                <div class="toys-landing-product__footer">
                                    <div class="toys-landing-product__footer-info">
                                        <p class="toys-landing-product__price">
                                            Обычная цена
                                            <strong>
                                                <s><?= WordHelper::numberFormat($offer['base_price'], 0) ?></s> ₽
                                            </strong>
                                        </p>
                                        <? foreach ($offer['stamp_levels'] as $stampLevel): ?>
                                            <p class="toys-landing-product__price">
                                                <span class="toys-landing-product__price-highlighed">
                                                    <?= $stampLevel['stamps'] ?> <img src="/upload/toys-landing/logo.png" alt="" width="19" height="19">
                                                </span>
                                                +
                                                <span class="toys-landing-product__price-highlighed">
                                                    <?= WordHelper::numberFormat($stampLevel['price'], 0) ?> ₽
                                                </span>
                                            </p>
                                        <? endforeach; ?>
                                    </div>

                                    <div class="toys-landing-product__footer-actions">
                                        <button class="toys-landing-product__a2c js-basket-add" onmousedown="try { rrApi.addToBasket(<?= $offer['product_id'] ?>); } catch (e) {}" data-offerid="<?= $offer['id'] ?>" data-url="/ajax/sale/basket/add/">
                                            В корзину
                                        </button>
                                        <a href="/shares/kopi-marki-pokupay-tovary-so-skidkoy-do-50-.html" class="b-link" target="_blank">Покупай за марки!</a>
                                    </div>
                                </div>
                            </div>
                        <? endif; ?>
                    <? endfor; ?>
                </div>
                <?
                $imgFirst = !$imgFirst;
                $itemNumber++;
                ?>
            <? endforeach; ?>
        </div>
    </section>
<? endforeach; ?>
