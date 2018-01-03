<?php
/**
 * @var RootCategoryRequest $rootCategoryRequest
 * @var CMain $APPLICATION
 */

use FourPaws\App\Templates\ViewsEnum;
use FourPaws\CatalogBundle\Dto\RootCategoryRequest;
use FourPaws\Catalog\Model\Category;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

global $APPLICATION;

/**
 * @var Category $category
 */
$category = $APPLICATION->IncludeComponent(
    'fourpaws:catalog.category.root',
    '',
    [
        'SECTION_CODE' => $rootCategoryRequest->getCategorySlug(),
        'SET_TITLE' => 'Y'
    ],
    $component
);
?>
    <div class="b-catalog">
        <div class="b-container b-container--catalog-main">
            <div class="b-catalog__wrapper-title">
                <?php
                $APPLICATION->IncludeComponent(
                    'fourpaws:breadcrumbs',
                    '',
                    [
                        'IBLOCK_SECTION' => $category,
                    ],
                    $component
                );
                ?>
                <nav class="b-breadcrumbs b-breadcrumbs--catalog-main">
                    <ul class="b-breadcrumbs__list">
                        <li class="b-breadcrumbs__item">
                            <a class="b-breadcrumbs__link" href="javascript:void(0);"
                               title="Главная">Главная</a>
                        </li>
                    </ul>
                </nav>
                <h1 class="b-title b-title--h1"><?= $category->getDisplayName() ?: $category->getName() ?></h1>
            </div>
            <?php
            /**
             * @todo Левое меню
             */
            ?>
            <aside class="b-filter b-filter--accordion">
                <div class="b-filter__wrapper">
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Корм для собак">Корм для собак</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Лакомства и витамины">Лакомства и витамины</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Одежда и обувь">Одежда и обувь</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Намордники, ошейники, поводки">Намордники, ошейники,
                            поводки</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Туалеты, лотки, совочки">Туалеты, лотки, совочки</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Товары для грумминга">Товары для грумминга</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Клетки, вольеры, двери">Клетки, вольеры, двери</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Товары для грумминга">Товары для грумминга</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Сумки, переноски">Сумки, переноски</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Игрушки">Игрушки</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Шлейки, ошейники, поводки">Шлейки, ошейники,
                            поводки</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Лежаки и домики">Лежаки и домики</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Гигиена и косметика">Гигиена и косметика</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Пеленки, подгузники, штанишки">Пеленки, подгузники,
                            штанишки</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Коррекция поведения">Коррекция поведения</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Средства от запаха и пятен">Средства от запаха и
                            пятен</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Защита от блох и клещей">Защита от блох и клещей</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Завели щенка">Завели щенка</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="b-accordion b-accordion--filter"><a
                                class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                                href="javascript:void(0);" title="Товары со скидкой">Товары со скидкой</a>
                        <div class="b-accordion__block js-dropdown-block">
                            <ul class="b-filter-link-list">
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Сухой">Сухой</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Консервы">Консервы</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);"
                                                                        title="Кормовая добавка и молоко">Кормовая
                                        добавка и
                                        молоко</a>
                                </li>
                                <li class="b-filter-link-list__item"><a class="b-filter-link-list__link"
                                                                        href="javascript:void(0);" title="Диетический">Диетический</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </aside>
            <?php
            $APPLICATION->ShowViewContent(ViewsEnum::CATALOG_CATEGORY_ROOT);
            /**
             * @todo brands in section
             */
            ?>
            <div class="b-catalog__brand">
                <div class="b-line b-line--catalog"></div>
                <section class="b-common-section">
                    <div class="b-common-section__title-box b-common-section__title-box--catalog b-common-section__title-box--catalog-popular">
                        <h2 class="b-title b-title--catalog b-title--catalog-popular">Популярные бренды</h2>
                    </div>
                    <div class="b-common-section__content b-common-section__content--catalog b-common-section__content--catalog-popular">
                        <div class="b-popular-brand">
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog" title="Hill's"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/hills.jpg"
                                         alt="Hill's" title="Hill's"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog"
                                   title="Perfect fit" href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/perfect-fit.jpg" alt="Perfect fit"
                                         title="Perfect fit"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog" title="Purina"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/purina.jpg" alt="Purina" title="Purina"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog"
                                   title="Whiskas"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/whiskas.jpg" alt="Whiskas" title="Whiskas"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog" title="Felix"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/felix.jpg"
                                         alt="Felix" title="Felix"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog"
                                   title="Royal Canin" href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/royal-canin.jpg" alt="Royal Canin"
                                         title="Royal Canin"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog" title="Brit"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/brit.jpg"
                                         alt="Brit" title="Brit"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog" title="Bozita"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/bozita.jpg" alt="Bozita" title="Bozita"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog"
                                   title="Eukanuba"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/eukanuba.jpg" alt="Eukanuba"
                                         title="Eukanuba"/>
                                </a>
                            </div>
                            <div class="b-popular-brand-item b-popular-brand-item--catalog">
                                <a class="b-popular-brand-item__link b-popular-brand-item__link--catalog" title="Acana"
                                   href="javascript:void(0);">
                                    <img class="b-popular-brand-item__image js-image-wrapper"
                                         src="/static/build/images/content/acana.jpg"
                                         alt="Acana" title="Acana"/>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <?php
        /**
         * @todo Просмотренные товары
         */
        ?>
        <div class="b-container">
            <div class="b-line b-line--pet"></div>
            <section class="b-common-section">
                <div class="b-common-section__title-box b-common-section__title-box--viewed">
                    <h2 class="b-title b-title--viewed">Просмотренные мной товары</h2>
                </div>
                <div class="b-common-section__content b-common-section__content--viewed js-scroll-viewed">
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image" src="/static/build/images/content/v-royal.png"
                                        alt="Роял Канин"
                                        title="Роял Канин"/></span><span
                                    class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">Роял Канин</span><span
                                        class="b-viewed-product__description">корм для собак крупных пород макси эдалт</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image" src="/static/build/images/content/v-moderna.png"
                                        alt="Moderna"
                                        title="Moderna"/></span><span class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">Moderna</span><span
                                        class="b-viewed-product__description">переноска с металической дверью и замком</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image" src="/static/build/images/content/v-hills.png"
                                        alt="Хиллс"
                                        title="Хиллс"/></span><span class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">Хиллс</span><span
                                        class="b-viewed-product__description">корм для кошек стерилайз</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image" src="/static/build/images/content/v-rungo.png"
                                        alt="Rungo"
                                        title="Rungo"/></span><span class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">Rungo</span><span
                                        class="b-viewed-product__description">маячок на ошейник круглый</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image"
                                        src="/static/build/images/content/v-cleancat.png"
                                        alt="CleanCat"
                                        title="CleanCat"/></span><span class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">CleanCat</span><span
                                        class="b-viewed-product__description">наполнитель для кошачьего туалета силик что-то там</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image"
                                        src="/static/build/images/content/v-pet-hobby.png"
                                        alt="Pet Hobby"
                                        title="Pet Hobby"/></span><span class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">Pet Hobby</span><span
                                        class="b-viewed-product__description">пуходёрка пластмассовая малая с каплечто-то там</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image"
                                        src="/static/build/images/content/v-pro-plan.png"
                                        alt="ПроПлан"
                                        title="ПроПлан"/></span><span class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">ПроПлан</span><span
                                        class="b-viewed-product__description">корм для кастрированных / стерилизованных кого-то</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image"
                                        src="/static/build/images/content/v-beaver-boardyard.png"
                                        alt="Бобровый дворик" title="Бобровый дворик"/></span><span
                                    class="b-viewed-product__description-wrap"><span class="b-viewed-product__label">Бобровый дворик</span><span
                                        class="b-viewed-product__description">ас-зоо домик №1 султан желтый</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image" src="/static/build/images/content/v-hills2.png"
                                        alt="Хиллс"
                                        title="Хиллс"/></span><span class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">Хиллс</span><span
                                        class="b-viewed-product__description">корм для собак крупных пород с курицей</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image" src="/static/build/images/content/v-pet-max.png"
                                        alt="Petmax"
                                        title="Petmax"/></span><span class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">Petmax</span><span
                                        class="b-viewed-product__description">подставка с мисками металл</span></span></a>
                    </div>
                </div>
            </section>
        </div>
    </div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
die();
