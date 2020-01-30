<?php

/**
 * @var DataLayerService $dataLayerService
 * @var SortsCollection  $sorts
 * @var Sorting          $sort
 * @var PhpEngine        $view
 * @var CMain            $APPLICATION
 */

use FourPaws\Catalog\Model\Sorting;
use FourPaws\CatalogBundle\Collection\SortsCollection;
use FourPaws\EcommerceBundle\Enum\DataLayer;
use FourPaws\EcommerceBundle\Service\DataLayerService;
use Symfony\Component\Templating\PhpEngine;

?>
<span class="b-catalog-filter__sort">
    <span class="b-catalog-filter__label b-catalog-filter__label--sort">Сортировать по</span>
    <span class="b-select b-select--sort js-filter-select">
        <select class="b-select__block b-select__block--sort js-filter-select" name="sort"
                onchange="<?= \str_replace(
                    ['"#category#"', '"'],
                    ['"По " + $.trim($(this).children("option:selected").text())', '\''],
                    $dataLayerService->renderSort(DataLayer::SORT_TYPE_CATALOG, '#category#')
                ) ?>">
              <?php foreach ($sorts as $sort) { ?>
                  <option value="<?= $sort->getValue() ?>" <?= $sort->isSelected() ? 'selected="selected"' : '' ?>>
                      <?= $sort->getName() ?>
                  </option>
              <?php } ?>
        </select>
        <span class="b-select__arrow"></span>
    </span>
</span>
