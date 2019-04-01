<? if (!empty($arResult)) { ?>
    <div class="landing-title landing-title_white">
        Победители
    </div>
    <div class="b-tab winners-landing__content">
        <div class="b-tab-title">
            <ul class="b-tab-title__list">
                <? foreach ($arResult as $key => $item): ?>
                    <li class="b-tab-title__item js-tab-item<? if ($key == count($arResult) - 1): ?> active<? endif; ?>">
                        <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-landing<?= $key; ?>">
                            <span class="b-tab-title__text"><?= $item['DATE']; ?></span>
                        </a>
                    </li>
                <? endforeach; ?>
            </ul>
        </div>
        <div class="b-tab-content">
            <? foreach ($arResult as $key => $item): ?>
                <div class="b-tab-content__container js-tab-content<? if ($key == count($arResult) - 1): ?> active<? endif; ?>" data-tab-content="winners-landing<?= $key; ?>">
                    <div class="winners-landing__list">
                        <? foreach ($item['WINNERS'] as $winner): ?>
                            <div class="item__wrap">
                                <div class="item">
                                    <div class="item__name" title="<?= $winner['NAME']; ?>"><?= $winner['NAME']; ?></div>
                                    <div class="item__dotes"></div>
                                    <div class="item__phone"><?= $winner['PROPERTY_PHONE_VALUE']; ?></div>
                                </div>
                            </div>
                        <? endforeach; ?>
                    </div>
                </div>
            <? endforeach; ?>
        </div>
    </div>
<? } ?>