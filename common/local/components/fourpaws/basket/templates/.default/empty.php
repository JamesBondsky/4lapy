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
    $emptyText = sprintf('%s, ваша корзина пуста. Посмотрите, что у нас есть в <a href="/catalog/" class="link--orange">каталоге</a>, или воспользуйтесь поиском.', $userService->getCurrentUser()->getName());
} else {
    $emptyText = 'В это сложно поверить, но ваша корзина пуста. Воспользуйтесь нашим <a href="/catalog/" class="link--orange">каталогом</a>, чтобы наполнить её.';
}

?>

<div class="b-shopping-cart">
    <div class="b-container">
        <main class="b-shopping-cart__main" role="main">
            <h1 class="b-title b-title--h1 b-title--shopping-cart" style="margin-top: 44px;"><?= $emptyText ?></h1>
        </main>
        <?php $APPLICATION->IncludeFile(
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
        ); ?>
    </div>
</div>
