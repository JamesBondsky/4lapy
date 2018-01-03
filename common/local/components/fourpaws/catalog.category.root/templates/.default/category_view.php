<?php
/**
 * @var CBitrixComponentTemplate $this
 * @var array $arParams
 * @var array $arResult
 * @var Category $category
 * @var Category $childCategory
 * @var Category[] $childCategories
 * @var Category $childChildCategory
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\BitrixOrm\Model\Exceptions\FileNotFoundException;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Model\Category;

foreach (array_values($childCategories) as $index => $childCategory) {
    ?>
    <section class="b-common-section" xmlns="http://www.w3.org/1999/html">
        <div class="b-common-section__title-box b-common-section__title-box--catalog">
            <h2 class="b-title b-title--catalog">
                <a href="<?= $childCategory->getSectionPageUrl() ?>"
                   title="<?= htmlspecialcharsbx($childCategory->getName()) ?>">
                    <?= $childCategory->getName() ?>
                </a>
            </h2>
        </div>
        <div class="b-common-section__content b-common-section__content--catalog js-catalog-main">
            <?php foreach ($childCategory->getChild() as $childChildCategory) {
                /* @todo ссылка на картинку-заглушку  */
                $src = '/empty';
                if ($childChildCategory->getPictureId()) {
                    try {
                        $picture = ResizeImageDecorator::createFromPrimary($childChildCategory->getPictureId())
                                                       ->setResizeWidth(180)
                                                       ->setResizeHeight(180);
                        $src = $picture->getSrc();
                    } catch (FileNotFoundException $e) {
                    }
                } ?>
                <div class="b-common-item b-common-item--catalog js-product-item">
                    <a class="b-common-item__link" href="<?= $childChildCategory->getSectionPageUrl() ?>"
                       title="<?= $childChildCategory->getName() ?>">
                        <span class="b-common-item__image-wrap b-common-item__image-wrap--catalog">
                                    <img class="b-common-item__image b-common-item__image--catalog js-weight-img"
                                         src="<?= $src ?>"
                                         alt="<?= $childChildCategory->getName() ?>"
                                         title="<?= $childChildCategory->getName() ?>"/>
                        </span>
                        <span class="b-common-item__description-wrap b-common-item__description-wrap--catalog">
                            <span class="b-clipped-text b-clipped-text--catalog">
                                <span><?= $childChildCategory->getName() ?></span>
                            </span>
                        </span>
                    </a>
                </div>
            <?php } ?>
        </div>
    </section>
    <?php
    if ($index + 1 < count($childCategories)) {
        ?>
        <div class="b-line b-line--catalog"></div>
        <?php
    }
}
