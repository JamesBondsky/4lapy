<?php if ($arResult['ITEMS']) : ?>
    <section class="questions-blackfriday">
        <div class="b-container">
            <h2 class="questions-blackfriday__title">Вопросы и ответы</h2>
            <div class="questions-blackfriday__accordion">
                <?php foreach ($arResult['ITEMS'] as $item): ?>
                    <div class="item-accordion">
                        <div class="item-accordion__header js-toggle-accordion active">
                            <span class="item-accordion__header-inner"><?=$item['NAME']?></span>
                        </div>
                        <div class="item-accordion__block js-dropdown-block" style="display: block;">
                            <div class="item-accordion__block-content">
                                <div class="item-accordion__block-text">
                                    <?=$item['PREVIEW_TEXT']?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>