<?php

use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\Catalog\Model\Category;
use FourPaws\Decorators\SvgDecorator;
use Symfony\Component\Templating\PhpEngine;

/**
 * @var CategoryCollection $landingCollection
 * @var Category $landing
 * @var string $currentPath
 * @var PhpEngine $view
 */ ?>
<div class="b-container b-container--catalog-filter">
    <div class="main_categories">
        <?php foreach ($landingCollection as $landing) { ?>
            <a class="main_categories__item<?= strpos($currentPath, $landing->getSectionPageUrl()) !== false ? '--active' : '' ?>"
               href="/<?= $landing->getCode() ?>/"><?= $landing->getName() ?></a>
        <?php } ?>
    </div>
</div>
<span class="b-icon">
    <?= new SvgDecorator('check', 10, 10) ?>
</span>
