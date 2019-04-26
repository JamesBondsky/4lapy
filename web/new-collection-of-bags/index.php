<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;

$APPLICATION->SetTitle('Новая коллекция сумок – переносок');
$APPLICATION->SetPageProperty('title', 'Новая коллекция сумок – переносок');
$APPLICATION->SetPageProperty('description', 'Новая коллекция сумок – переносок');

global $APPLICATION;

$offerData = [
    [
        'TITLE' => 'Модные сумки',
        'SUBTITLE' => 'рыбатекст рыбатекст рыбатекст рыбатекст рыбатекст рыбатекст рыбатекст',
        'IMAGE' => '/upload/vetapteka_images/banner-free processing.png',
        'OFFERS' => [
            1029856,
            1029857,
            1029858,
            1029861,
            1029862,
            1029863,
            1029864,
            1029884,
            1029885,
            1029886,
            1029887
        ]
    ],
    [
        'TITLE' => 'Спортивные сумки',
        'SUBTITLE' => 'рыбатекст рыбатекст рыбатекст рыбатекст рыбатекст рыбатекст рыбатекст',
        'IMAGE' => '/upload/vetapteka_images/banner-free processing.png',
        'OFFERS' => [
            1029852,
            1029853,
            1029855,
            1024100,
            1029865,
            1029866
        ]
    ],
    [
        'TITLE' => 'Классические сумки',
        'SUBTITLE' => 'рыбатекст рыбатекст рыбатекст рыбатекст рыбатекст рыбатекст рыбатекст',
        'IMAGE' => '/upload/vetapteka_images/banner-free processing.png',
        'OFFERS' => [
            1029850,
            1024096,
            1029851,
            1029859,
            1029860,
            1029867,
            1029868,
            1029869,
            1029839,
            1029840,
            1029841,
            1029842,
            1029843,
            1029844,
            1029845,
            1029846,
            1029847,
            1029848,
            1024085,
            1024087,
            1024088,
            1024089,
            1024090,
            1032132,
            1032133,
            1032134,
            1032135,
        ]
    ],
    [
        'TITLE' => 'Сумки 2 в 1',
        'SUBTITLE' => 'рыбатекст рыбатекст рыбатекст рыбатекст рыбатекст рыбатекст рыбатекст',
        'IMAGE' => '/upload/vetapteka_images/banner-free processing.png',
        'OFFERS' => [
            1029849,
            1024091
        ]
    ],
];
?>
    <div class="b-container b-container--news">
        <div class="b-news">
            <h1 class="b-title b-title--h1">Новая коллекция сумок – переносок</h1>
            <div class="b-container">
                <?
                foreach ($offerData as $offerDatum) {
                    $offerCollection = (new OfferQuery())
                        ->withFilter(['=XML_ID' => $offerDatum['OFFERS']])
                        ->exec();
                    $productCollection = new ArrayCollection();
                    /** @var Offer $offer */
                    foreach ($offerCollection as $offer) {
                        $product = $offer->getProduct();
                        $productCollection->set($product->getId(), $product);
                    }
                    ?>
                    <section class="b-common-section" data-url="">
                        <?//b-fleas-protection-banner--new-collection-of-bags - новый класс, для него писать стили, чтоб не затереть?>
                        <div class="b-fleas-protection-banner b-fleas-protection-banner--catalog b-fleas-protection-banner--new-collection-of-bags">
                            <div class="b-fleas-protection-banner__bg" style="background-image: url('<?=$offerDatum['IMAGE']?>')"></div>
                            <div class="b-fleas-protection-banner__content">
                                <div class="b-fleas-protection-banner__title"><?=$offerDatum['TITLE']?></div>
                                <div class="b-fleas-protection-banner__subtitle"><?=$offerDatum['SUBTITLE']?></div>
                            </div>
                        </div>
                        <div class="b-common-wrapper b-common-wrapper--visible js-catalog-wrapper">
                            <?
                            foreach ($productCollection as $product) {
                                $i++;
                                $APPLICATION->IncludeComponent(
                                    'fourpaws:catalog.element.snippet',
                                    '',
                                    [
                                        'PRODUCT' => $product,
                                        'GOOGLE_ECOMMERCE_TYPE' => 'Новая коллекция сумок – переносоку',
                                    ],
                                    null,
                                    ['HIDE_ICONS' => 'Y']
                                );
                            }
                            ?>
                        </div>
                    </section>
                    <?
                }
                ?>
            </div>
        </div>
    </div>
<?php

/**
 * Просмотренные товары
 */
$APPLICATION->IncludeComponent(
    'bitrix:main.include',
    '',
    [
        'AREA_FILE_SHOW' => 'file',
        'PATH' => '/local/include/blocks/viewed_products.php',
        'EDIT_TEMPLATE' => '',
    ],
    null,
    [
        'HIDE_ICONS' => 'Y',
    ]
);
?>
<? require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');