<?php if ($arResult['ITEMS']) : ?>
    <section class="brands-blackfriday">
        <div class="b-container">
            <div class="title-blackfriday">Бренды со скидками до 50%</div>
            <div class="b-common-section__content b-common-section__content--popular-brand">
                <div class="b-popular-brand">
                    <?php foreach ($arResult['ITEMS'] as $item): ?>
                        <div class="b-popular-brand-item">
                            <a class="b-popular-brand-item__link" title="<?=$item['NAME']?>" href="<?=$item['LINK']?>" target="_blank">
                                <img class="b-popular-brand-item__image js-image-wrapper js-lazy lazy-loaded" alt="<?=$item['NAME']?>" title="<?=$item['NAME']?>"
                                     src="<?=$item['PREVIEW_PICTURE']?>" style="">
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>