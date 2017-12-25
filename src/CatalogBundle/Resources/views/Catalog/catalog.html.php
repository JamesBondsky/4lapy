<?php
/**
 * @var CatalogCategorySearchRequestInterface $catalogRequest
 * @var ProductSearchResult                   $productSearchResult
 * @var PhpEngine                             $view
 * @var CMain                                 $APPLICATION
 */

use FourPaws\CatalogBundle\Dto\CatalogCategorySearchRequestInterface;
use FourPaws\Search\Model\ProductSearchResult;
use Symfony\Component\Templating\PhpEngine;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
?>
    <div class="b-catalog js-preloader-fix">
        <?= $view->render('FourPawsCatalogBundle:Catalog:catalog.filter.container.html.php', [
            'catalogRequest'      => $catalogRequest,
            'productSearchResult' => $productSearchResult,
        ]) ?>
        <div class="b-container">
            <div class="b-line b-line--pet">
            </div>
            <section class="b-common-section">
                <div class="b-common-section__title-box b-common-section__title-box--viewed">
                    <h2 class="b-title b-title--viewed">Просмотренные мной товары
                    </h2>
                </div>
                <div class="b-common-section__content b-common-section__content--viewed js-scroll-viewed">
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image" src="/static/build/images/content/v-royal.png"
                                        alt="Роял Канин" title="Роял Канин"/></span><span
                                    class="b-viewed-product__description-wrap"><span class="b-viewed-product__label">Роял Канин</span><span
                                        class="b-viewed-product__description">корм для собак крупных пород макси эдалт</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image" src="/static/build/images/content/v-moderna.png"
                                        alt="Moderna" title="Moderna"/></span><span
                                    class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">Moderna</span><span
                                        class="b-viewed-product__description">переноска с металической дверью и замком</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image" src="/static/build/images/content/v-hills.png"
                                        alt="Хиллс" title="Хиллс"/></span><span
                                    class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">Хиллс</span><span
                                        class="b-viewed-product__description">корм для кошек стерилайз</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image" src="/static/build/images/content/v-rungo.png"
                                        alt="Rungo" title="Rungo"/></span><span
                                    class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">Rungo</span><span
                                        class="b-viewed-product__description">маячок на ошейник круглый</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image"
                                        src="/static/build/images/content/v-cleancat.png"
                                        alt="CleanCat" title="CleanCat"/></span><span
                                    class="b-viewed-product__description-wrap"><span class="b-viewed-product__label">CleanCat</span><span
                                        class="b-viewed-product__description">наполнитель для кошачьего туалета силик что-то там</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image"
                                        src="/static/build/images/content/v-pet-hobby.png"
                                        alt="Pet Hobby" title="Pet Hobby"/></span><span
                                    class="b-viewed-product__description-wrap"><span class="b-viewed-product__label">Pet Hobby</span><span
                                        class="b-viewed-product__description">пуходёрка пластмассовая малая с каплечто-то там</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image"
                                        src="/static/build/images/content/v-pro-plan.png"
                                        alt="ПроПлан" title="ПроПлан"/></span><span
                                    class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">ПроПлан</span><span
                                        class="b-viewed-product__description">корм для кастрированных / стерилизованных кого-то</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image"
                                        src="/static/build/images/content/v-beaver-boardyard.png" alt="Бобровый дворик"
                                        title="Бобровый дворик"/></span><span
                                    class="b-viewed-product__description-wrap"><span class="b-viewed-product__label">Бобровый дворик</span><span
                                        class="b-viewed-product__description">ас-зоо домик №1 султан желтый</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image" src="/static/build/images/content/v-hills2.png"
                                        alt="Хиллс" title="Хиллс"/></span><span
                                    class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">Хиллс</span><span
                                        class="b-viewed-product__description">корм для собак крупных пород с курицей</span></span></a>
                    </div>
                    <div class="b-viewed-product"><a class="b-viewed-product__link" href="javascript:void(0);"
                                                     title=""><span class="b-viewed-product__image-wrap"><img
                                        class="b-viewed-product__image" src="/static/build/images/content/v-pet-max.png"
                                        alt="Petmax" title="Petmax"/></span><span
                                    class="b-viewed-product__description-wrap"><span
                                        class="b-viewed-product__label">Petmax</span><span
                                        class="b-viewed-product__description">подставка с мисками металл</span></span></a>
                    </div>
                </div>
            </section>
        </div>
        <div class="b-preloader b-preloader--catalog">
            <div class="b-preloader__spinner">
                <img class="b-preloader__image" src="/static/build/images/inhtml/spinner.svg" alt="spinner" title=""/>
            </div>
        </div>
    </div>
<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
