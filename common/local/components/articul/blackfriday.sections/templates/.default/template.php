<section class="products-blackfriday">
    <div class="b-container">
        <div class="title-blackfriday">Скидки до 50% на 2 500 зоотоваров!</div>
        <?php if ($arResult['SECTION_WITH_ELEMENTS']): ?>
            <?php foreach ($arResult['SECTION_WITH_ELEMENTS'] as $section): ?>
                <div class="banner-blackfriday banner-blackfriday_full">
                    <div class="banner-blackfriday__bg-wrap">
                        <div class="banner-blackfriday__bg banner-blackfriday__bg_desktop" style="background-image: url('<?=$section['DESKTOP_PICTURE']?>')"></div>
                        <div class="banner-blackfriday__bg banner-blackfriday__bg_tablet" style="background-image: url('<?=$section['TABLET_PICTURE']?>')"></div>
                        <div class="banner-blackfriday__bg banner-blackfriday__bg_mobile" style="background-image: url('<?=$section['MOBILE_PICTURE']?>')"></div>
                    </div>
                    <?php if ($section['LABEL_RIGHT']) : ?>
                        <div class="banner-blackfriday__label banner-blackfriday__label_right">
                            <img src="/bf/img/sale-bf.svg"/>
                            <span class="sale-label">-<?=$section['DISCOUNT_SIZE']?>%</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($section['LABEL_LEFT']) : ?>
                        <div class="banner-blackfriday__label banner-blackfriday__label_left">
                            <img src="/bf/img/sale-bf.svg"/>
                            <span class="sale-label">-<?=$section['DISCOUNT_SIZE']?>%</span>
                        </div>
                    <?php endif; ?>
                    <div class="banner-blackfriday__content">
                        <div class="banner-blackfriday__title"><?=$section['SECTION_NAME']?></div>
                        <a href="<?=$section['LINK']?>" target="_blank" class="banner-blackfriday__btn">Посмотреть все</a>
                    </div>
                </div>
                <div class="products-blackfriday__list-products">
                    <?php foreach ($section['ITEMS'] as $item): ?>
                        <div class="b-common-item b-common-item--catalog">
                            <a class="b-common-item__link" href="<?=$item['LINK']?>" title="Средства гигиены и косметика">
                        <span class="b-common-item__image-wrap b-common-item__image-wrap--catalog">
                            <img src="<?=$item['PREVIEW_PICTURE']?>"
                                 class="b-common-item__image b-common-item__image--catalog"
                                 alt="<?=$item['NAME']?>"
                                 title="<?=$item['NAME']?>">
                        </span>
                                <span class="b-common-item__description-wrap b-common-item__description-wrap--catalog">
                            <span class="b-clipped-text b-clipped-text--catalog">
                                <span><?=$item['NAME']?></span>
                            </span>
                        </span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if ($arResult['EMPTY_SECTIONS']): ?>
            <div class="products-blackfriday__list-banner">
                <?php foreach ($arResult['EMPTY_SECTIONS'] as $emptySection): ?>
                    <div class="item">
                        <div class="banner-blackfriday">
                            <div class="banner-blackfriday__bg-wrap">
                                <div class="banner-blackfriday__bg banner-blackfriday__bg_desktop" style="background-image: url('<?=$emptySection['DESKTOP_PICTURE']?>')"></div>
                                <div class="banner-blackfriday__bg banner-blackfriday__bg_tablet" style="background-image: url('<?=$emptySection['TABLET_PICTURE']?>')"></div>
                                <div class="banner-blackfriday__bg banner-blackfriday__bg_mobile" style="background-image: url('<?=$emptySection['MOBILE_PICTURE']?>')"></div>
                            </div>
                            <div class="banner-blackfriday__content">
                                <div class="banner-blackfriday__title"><?=$emptySection['NAME']?></div>
                                <a href="<?=$emptySection['UF_LINK']?><" target="_blank" class="banner-blackfriday__btn">Посмотреть все</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
