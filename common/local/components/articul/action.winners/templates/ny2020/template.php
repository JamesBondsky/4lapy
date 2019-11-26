<?php

/**
 * @var array $arResult
 */

if (!empty($arResult)): ?>
  <div class="container-landing">
    <div class="winners-ny2020__inner">
      <div class="title-ny2020">
        Победители
      </div>
      <div class="b-tab winners-ny2020__content">
        <div class="b-tab-title">
          <ul class="b-tab-title__list">
              <?php foreach ($arResult as $key => $item): ?>
                <li class="b-tab-title__item js-tab-item <?= ($key === (count($arResult) - 1)) ? 'active' : '' ?>">
                  <a class="b-tab-title__link js-tab-link" href="javascript:void(0);" data-tab="winners-ny2020-<?= $key ?>">
                    <span class="b-tab-title__text"><?= $item['DATE'] ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
          </ul>
        </div>
        <div class="b-tab-content">
            <?php foreach ($arResult as $key => $item): ?>
              <div class="b-tab-content__container js-tab-content <?= ($key === (count($arResult) - 1)) ? 'active' : '' ?>" data-tab-content="winners-ny2020-<?= $key ?>">
                <div class="winners-ny2020__list">
                    <?php foreach ($item['WINNERS'] as $winner): ?>
                      <div class="item__wrap">
                        <div class="item">
                          <div class="item__name" title="<?= $winner['NAME'] ?>">
                            <div class="item__icon item__icon_cup"></div>
                              <?= $winner['NAME'] ?>
                          </div>
                          <div class="item__phone"><?= $winner['PROPERTY_PHONE_VALUE'] ?></div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
<?php else: ?>
    <?php $this->SetViewTarget('empty-winners'); ?>display:none;<?php $this->EndViewTarget(); ?>
<?php endif; ?>
