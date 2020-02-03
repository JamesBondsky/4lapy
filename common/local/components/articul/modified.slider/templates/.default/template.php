<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

if (!\is_array($arResult['items']) || empty($arResult['items'])) {
    return;
}
?>

<section class="b-promo-banner">
  <div class="b-container">
    <div class="b-promo-banner__list js-promo-banner">
        <?php foreach ($arResult['items'] as $key => $item) { ?>
          <div class="b-promo-banner-item<?= $item['additionalClasses'] ?> <?= ($item['externalId'] === 'festival') ? 'b-promo-banner-item--festival' : '' ?> <?= $item['link'] ?> <?= $item['hideLogoMobile'] ? 'b-promo-banner-item--no-mobile-logo' : '' ?><?= ($item['leftColor']) ? ' custom-banner-item-' . $item['leftColor'] : '' ?>">
            <div class="b-promo-banner-item__content">
              <div class="b-promo-banner-item__left">
                <div class="b-promo-banner-item__logo" <?php if (!empty($item['leftSvg'])) { ?>style="background: url(<?= $arResult['files'][$item['leftSvg']] ?>) no-repeat center; background-size: 92%; height: 62px;<?php } ?>"></div><?php // костыль для баннера "новая коллекция" ?>
                  <?php if ($item['leftColor']) { ?>
                    <style>
                      .custom-banner-item-<?= $item['leftColor'] ?>:before {
                        background-color: <?= $item['hashLeftColor'] ?> !important;
                      }
                    </style>
                  <?php } ?>
                <div class="b-promo-banner-item__img">
                  <img src="<?= $arResult['files'][$item['previewImg']] ?>" alt=""/>
                </div>
              </div>
              <div class="b-promo-banner-item__descr"><?= $item['previewText'] ?></div>
              <div class="b-promo-banner-item__link-wrap">
                <a class="b-promo-banner-item__link" href="<?= $item['link'] ?>" <?= $item['buttonColor'] ? sprintf('style="background-color: %s"', $item['buttonColor']) : '' ?>>
                    <?php if ($item['buttonText']) { ?>
                        <?= $item['buttonText'] ?>
                    <?php } elseif ($item['externalId'] === 'festival') { ?>
                      Я пойду
                    <?php } else { ?>
                      Подробнее
                    <?php } ?>
                </a>
              </div>
            </div>
          </div>
        <?php } ?>
    </div>
  </div>
</section>
