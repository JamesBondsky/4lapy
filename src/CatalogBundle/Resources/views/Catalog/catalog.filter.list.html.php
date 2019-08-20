<?php

/**
 * @var ChildCategoryRequest $catalogRequest
 * @var FilterCollection $filters
 * @var FilterBase       $filter
 * @var PhpEngine        $view
 * @var Variant          $variant
 * @var CMain            $APPLICATION
 */

use FourPaws\Catalog\Collection\FilterCollection;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Filter\ClothingSizeFilter;
use FourPaws\Catalog\Model\Filter\PriceFilter;
use FourPaws\Catalog\Model\Variant;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\Decorators\SvgDecorator;
use Symfony\Component\Templating\PhpEngine;

$category = $catalogRequest->getCategory();

foreach ($filters as $filter) {
    if ($filter instanceof PriceFilter) { ?>
        <div class="b-filter__block">
            <div class="b-title b-title--filter-header">
                <?= $filter->getName() ?>
            </div>
            <div class="b-range js-filter-input">
                <div class="b-range__price-block">
                    <input
                            class="b-input__input-field b-input__input-field--price b-input__input-field--min js-price-min"
                            type="text"
                            data-min="<?= $filter->getMinValue() ?>"
                            value="<?= $filter->getFromValue() ?: $filter->getMinValue() ?>"
                            name="<?= $filter->getFromFilterCode() ?>"/>
                    <span class="b-range__line-input">-</span>
                    <input
                            class="b-input__input-field b-input__input-field--price b-input__input-field--max js-price-max"
                            type="text"
                            data-max="<?= $filter->getMaxValue() ?>"
                            value="<?= $filter->getToValue() ?: $filter->getMaxValue() ?>"
                            name="<?= $filter->getToFilterCode() ?>"/>
                </div>
                <div class="b-range__line js-slider-range"></div>
            </div>
        </div>
        <?php
        continue;
    }

    if ($filter instanceof FilterBase) {
        if ($isBrand
            && \in_array($filter->getFilterCode(), [
                'Sections',
                'Categories'
            ])) {
            continue;
        } ?>
        <div class="b-filter__block">
            <div class="b-title b-title--filter-header">
                <?= $filter->getName() ?>
                <? if($filter instanceof ClothingSizeFilter && $category->isShowFitting()) { ?>
                    <a class="js-scroll-to-size-select" href="javascript:void(0)">Узнать размер</a>
                <? } ?>
            </div>
            <?php if ($filter->isShowWithPicture()) { ?>
                <div class="size_filter js-size-filter color_filter js-color-filter js-accordion-filter js-filter-checkbox quoter">
                    <?php
                    /**
                     * @var int     $id
                     * @var Variant $variant
                     */
                    foreach ($filter->getAvailableVariants() as $id => $variant) {
                        if (($image = $variant->getImageSrc(40, 40)) || $variant->getColor()) {
                            $style = $image
                                ? \sprintf('background-image: url(%s)', $image)
                                : \sprintf('background-color: #%s;', \ltrim($variant->getColor(), ' #'));
                            ?>
                            <label class="color_filter__item<?= $variant->isChecked() ? '--checked' : '' ?> js-color-filter-item"
                                <?= $variant->getOnclick()
                                    ? \sprintf(
                                        'onclick="%s"',
                                        $variant->getOnclick()
                                    )
                                    : ''
                                ?>
                                   style="<?= $style ?>">
                                <input <?= $variant->isChecked() ? 'checked' : '' ?>
                                        class="js-checkbox-change js-filter-control js-filter-select"
                                        id="<?= $filter->getFilterCode() ?>-<?= $id ?>"
                                        type="checkbox"
                                        name="<?= $filter->getFilterCode() ?>"
                                        value="<?= $variant->getValue() ?>">
                            </label>
                        <?php } else { ?>
                            <label class="size_filter__item<?= $variant->isChecked() ? '--active' : '' ?> js-size-filter-item"
                                <?= $variant->getOnclick()
                                    ? \sprintf(
                                        'onclick="%s"',
                                        $variant->getOnclick()
                                    )
                                    : ''
                                ?>>
                                <input <?= $variant->isChecked() ? 'checked' : '' ?>
                                        class="js-checkbox-change js-filter-control js-filter-select"
                                        id="<?= $filter->getFilterCode() ?>-<?= $id ?>"
                                        type="checkbox"
                                        name="<?= $filter->getFilterCode() ?>"
                                        value="<?= $variant->getValue() ?>">
                                <?= $variant->getName() ?>
                            </label>
                        <?php }
                    } ?>
                </div>
            <?php } else { ?>
                <ul class="b-filter-link-list b-filter-link-list--filter js-accordion-filter js-filter-checkbox">
                    <?php foreach ($filter->getAvailableVariants() as $id => $variant) { ?>
                        <li class="b-filter-link-list__item">
                            <label class="b-filter-link-list__label"
                                <?= $variant->getOnclick()
                                    ? \sprintf(
                                        'onclick="%s"',
                                        $variant->getOnclick()
                                    )
                                    : ''
                                ?>>
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
                    <?php } ?>
                </ul>
            <?php } ?>
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
