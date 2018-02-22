<?php
/**
 * Created by PhpStorm.
 * Date: 29.12.2017
 * Time: 16:39
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 *
 * @var $component BasketComponent
 */

use FourPaws\Components\BasketComponent;

$userService = $component->getCurrentUserService();

if ($userService->isAuthorized()) {
    $emptyText = sprintf('%s, ваша корзина пуста. Посмотрите, что у нас есть в <a href="/catalog/">каталоге, или воспользуйтесь поиском.</a>', $userService->getCurrentUser()->getName());
} else {
    $emptyText = 'В это сложно поверить, но ваша корзина пуста. Воспользуйтесь нашим <a href="/catalog/">каталогом, чтобы наполнить её.</a>';
}

?>

<div class="b-shopping-cart">
    <div class="b-container">
        <h1 class="b-title b-title--h1 b-title--shopping-cart"><?= $emptyText ?></h1>
        <main class="b-shopping-cart__main" role="main">

        </main><?php
        /**
         * Просмотренные товары
         */
        $APPLICATION->IncludeFile(
            'blocks/components/viewed_products.php',
            [
                'WRAP_CONTAINER_BLOCK' => 'N',
                'WRAP_SECTION_BLOCK' => 'Y',
                'SHOW_TOP_LINE' => 'Y',
                'SHOW_BOTTOM_LINE' => 'N',
            ],
            [
                'SHOW_BORDER' => false,
                'NAME' => 'Блок просмотренных товаров',
                'MODE' => 'php',
            ]
        );
    ?></div>
</div>
