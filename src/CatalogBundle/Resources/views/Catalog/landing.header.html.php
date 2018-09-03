<?php

use FourPaws\CatalogBundle\Dto\ChildCategoryRequest;
use FourPaws\Decorators\SvgDecorator;
use Symfony\Component\Templating\PhpEngine;

/**
 * @var ChildCategoryRequest $catalogRequest
 * @var string $currentPath
 * @var PhpEngine $view
 */ ?>
<div class="b-container" id="catalog">
    <div class="main_categories">
        <?php foreach ($catalogRequest->getLandingCollection() as $landing) { ?>
            <a class="main_categories__item<?= strpos($catalogRequest->getCurrentPath(), $landing->getSectionPageUrl()) !== false ? '--active' : '' ?>"
               href="<?= $catalogRequest->getCategoryPathByCategory($landing) ?>"><?= $landing->getName() ?></a>
        <?php } ?>
    </div>
</div>
<span class="b-icon">
    <?= new SvgDecorator('check', 10, 10) ?>
</span>
