<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

?>

<div class="container-landing">
  <div class="winners-ny2020__inner">
    <div class="title-ny2020">
      Победители
    </div>
    <div class="b-tab winners-ny2020__content">
      <div class="b-tab-title">
        <ul class="b-tab-title__list">
            <?php $i = 0; ?>
            <?php foreach ($arResult['periods'] as $periodId => $period) { ?>
                <?php $i++ ?>
              <li class="b-tab-title__item js-tab-item">
                <a class="b-tab-title__link <?= ($i === $arResult['totalCount']) ? 'b-tab-title__link' : '' ?> js-tab-link" href="javascript:void(0);" data-tab="winners-ny2020-<?= $periodId ?>">
                  <span class="b-tab-title__text"><?= $period['title'] ?></span>
                </a>
              </li>
            <?php } ?>
        </ul>
      </div>
      <div class="b-tab-content">
          <?php foreach ($arResult['periods'] as $periodId => $period) { ?>
            <div class="b-tab-content__container js-tab-content" data-tab-content="winners-ny2020-<?= $periodId ?>">
              <div class="winners-ny2020__list">
                  <?php foreach ($period['winners'] as $winner) { ?>
                    <div class="item__wrap">
                      <div class="item">
                        <div class="item__name" title="Голубева	Наталья">
                          <div class="item__icon item__icon_cup"></div>
                            <?= $winner['name'] ?>
                        </div>
                        <div class="item__phone"><?= $winner['phone'] ?></div>
                      </div>
                    </div>
                  <?php } ?>
              </div>
            </div>
          <?php } ?>
      </div>
    </div>
  </div>
</div>
