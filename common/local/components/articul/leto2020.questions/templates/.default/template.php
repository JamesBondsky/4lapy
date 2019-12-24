<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div class="b-container">
  <h2 class="title-leto2020 title-leto2020_blue">Вопросы и ответы</h2>
  <div class="questions-leto2020__accordion">
    <?php foreach ($arResult as $key => $element) { ?>
      <div class="item-accordion">
        <div class="item-accordion__header js-toggle-accordion <?= ($key === 0) ? 'active' : '' ?>">
          <span class="item-accordion__header-inner"><?= $element['name'] ?></span>
        </div>
        <div class="item-accordion__block js-dropdown-block" <?= ($key === 0) ? 'style="display: block;"' : '' ?>>
          <div class="item-accordion__block-content">
            <div class="item-accordion__block-text">
              <?= $element['text'] ?>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
</div>
