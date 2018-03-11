<?php

/**
 * @var FilterCollection $filters
 * @var FilterBase       $filter
 * @var PhpEngine        $view
 * @var CMain            $APPLICATION
 */

use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Filter\ActionsFilter;
use FourPaws\Catalog\Model\Filter\PriceFilter;
use FourPaws\Catalog\Model\Filter\RangeFilterInterface;
use FourPaws\Catalog\Model\Variant;
use FourPaws\Decorators\SvgDecorator;
use Symfony\Component\Templating\PhpEngine;

foreach ($filters as $filter) {
    if ($filter instanceof PriceFilter) {
        ?>
        <div class="b-filter__block">
            <h3 class="b-title b-title--filter-header">
                <?= $filter->getName() ?>
            </h3>
            <div class="b-range js-filter-input">
                <div class="b-range__price-block">
                    <input
                            class="b-input__input-field b-input__input-field--price b-input__input-field--min js-price-min"
                            type="text"
                            data-min="<?= $filter->getMinValue() ?>"
                            value="<?= $filter->getFromValue() ?: $filter->getMinValue() ?>"
                            name="<?= $filter->getFromFilterCode() ?>" />
                    <span class="b-range__line-input">-</span>
                    <input
                            class="b-input__input-field b-input__input-field--price b-input__input-field--max js-price-max"
                            type="text"
                            data-max="<?= $filter->getMaxValue() ?>"
                            value="<?= $filter->getToValue() ?: $filter->getMaxValue() ?>"
                            name="<?= $filter->getToFilterCode() ?>" />
                </div>
                <div class="b-range__line js-slider-range"></div>
            </div>
        </div>
        <?php
        continue;
    }
    if ($filter instanceof RangeFilterInterface) {
        continue;
    }
    if ($filter instanceof ActionsFilter) {
        continue;
    }
    if (!$filter->hasAvailableVariants()) {
        continue;
    }
    if ($filter instanceof FilterBase) {
        ?>
        <div class="b-filter__block">
            <h3 class="b-title b-title--filter-header">
                <?= $filter->getName() ?>
            </h3>
            <ul class="b-filter-link-list b-filter-link-list--filter js-accordion-filter js-filter-checkbox">
                <?php
                /**
                 * @var Variant $variant
                 */
                foreach ($filter->getAvailableVariants() as $id => $variant) {
                    ?>
                    <li class="b-filter-link-list__item">
                        <label class="b-filter-link-list__label">
                            <input class="b-filter-link-list__checkbox js-checkbox-change js-filter-control"
                                   type="checkbox"
                                   name="<?= $filter->getFilterCode() ?>"
                                   value="<?= $variant->getValue() ?>"
                                   id="<?= $filter->getFilterCode() ?>-<?= $id ?>"
                                <?= $variant->isChecked() ? 'checked' : '' ?>
                            />
                            <a class="b-filter-link-list__link b-filter-link-list__link--checkbox"
                               href="javascript:void(0);"
                               title="<?= $variant->getName() ?>"
                            ><?= $variant->getName() ?></a>
                        </label>
                    </li>
                    <?php
                } ?>
            </ul>
            <a class="b-link b-link--filter-more js-open-filter-all"
               href="javascript:void(0);" title="Показать все">
                Показать все
                <span class="b-icon b-icon--more">
                    <?= new SvgDecorator('icon-arrow-down', 10, 10) ?>
                </span>
            </a>
        </div>
        <?php
    }
}
?>
