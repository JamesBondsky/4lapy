<div class="b-container">
    <div class="title-leto2020">Фото победителей</div>
    <div class="photo-winners-leto2020__list-wrap">
        <div class="photo-winners-leto2020__list" data-list-photo-winners-leto2020="true">
            <?php foreach ($arResult['ITEMS'] as $item) : ?>
            <div class="item">
                <div class="item__top">
                    <div class="item__photo" style="background-image: url('<?=$item['PREVIEW_PICTURE']?>')">
                        <img src="/leto2020/img/bg-photo.png" alt="" />
                    </div>
                    <div class="item__prizes <?php if ($item['PROPERTY_ICON_VALUE']) : ?>item__prizes_<?=$item['PROPERTY_ICON_VALUE']?><?php endif; ?>"></div>
                </div>
                <div class="item__name"><?=$item['NAME']?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<div class="photo-winners-leto2020__branch-left"></div>
<div class="photo-winners-leto2020__branch-right"></div>
