<?php


/**
 * @var SortsCollection $sorts
 * @var PhpEngine $view
 * @var CMain $APPLICATION
 */

use Symfony\Component\Templating\PhpEngine;
use FourPaws\CatalogBundle\Collection\SortsCollection;
use FourPaws\Catalog\Model\Sorting;

?>
<span class="b-catalog-filter__sort">
    <span class="b-catalog-filter__label b-catalog-filter__label--sort">Сортировать по</span>
    <span class="b-select b-select--sort js-filter-select">
        <select class="b-select__block b-select__block--sort js-filter-select" name="sort">
              <?php
              /**
               * @var Sorting $sort
               */
              foreach ($sorts as $sort) {
                  ?>
                  <option value="<?= $sort->getValue() ?>" <?= $sort->isSelected(
                  ) ? 'selected="selected"' : '' ?>><?= $sort->getName() ?></option>
                  <?php
              }
              ?>
        </select>
        <span class="b-select__arrow"></span>
    </span>
</span>
