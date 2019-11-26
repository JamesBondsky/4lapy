<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div class="b-container">
  <h2 class="questions-ny2020__title">Вопросы и ответы</h2>
  <div class="questions-ny2020__accordion">
    <?php foreach ($arResult as $element) { ?>
      <div class="item-accordion">
        <div class="item-accordion__header js-toggle-accordion">
          <span class="item-accordion__header-inner"><?= $element['name'] ?></span>
        </div>
        <div class="item-accordion__block js-dropdown-block">
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
