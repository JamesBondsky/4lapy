<?php

use FourPaws\Catalog\Model\Category;
use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\Decorators\SvgDecorator;
use Symfony\Component\Templating\PhpEngine;

/**
 * @var ChildCategoryRequest $catalogRequest
 * @var string               $currentPath
 * @var PhpEngine            $view
 * @var Category             $landing
 */ ?>
<div class="b-container" id="catalog">
    <div class="main_categories">
        <?php foreach ($catalogRequest->getLandingCollection() as $landing) { ?>
            <a class="main_categories__item<?= $landing->isActiveLandingCategory() ? '--active' : '' ?>"
               href="<?= $catalogRequest->getCategoryPathByCategory($landing) ?>"><?= $landing->getName() ?></a>
        <?php } ?>
    </div>
</div>
<span class="b-icon">
    <?= new SvgDecorator('check', 10, 10) ?>
</span>
