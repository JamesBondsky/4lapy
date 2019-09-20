<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use Doctrine\Common\Collections\ArrayCollection;

global $APPLICATION;

$APPLICATION->SetPageProperty('title', 'Распродажа до -50% на 2000 товаров');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle("Распродажа до -50% на 2000 товаров");

$offerData = [
    [
        'TITLE'        => 'Сумки-переноски для кошек и собак',
        'DETAIL_LINK'  => '/catalog/sobaki/sumki-perenoski-sobaki/',
        'IMAGE'        => '/upload/summer-sale/bags.jpg',
        'IMAGE_MOBILE' => '/upload/summer-sale/bags--small.jpg',
        'OFFERS'       => [
            1029884,
            1029857,
            1029862,
            1029856,
            1030516,
            1021099,
            1005549,
            1020008,
        ]
    ],
    [
        'TITLE'        => 'Лежаки для кошек и собак',
        'DETAIL_LINK'  => '/catalog/sobaki/lezhaki-i-domiki/',
        'IMAGE'        => '/upload/summer-sale/sun-beds.jpg',
        'IMAGE_MOBILE' => '/upload/summer-sale/sun-beds--small.jpg',
        'OFFERS'       => [
            1029819,
            1029815,
            1029810,
            1029812,
            1029805,
            1029829,
            1029824,
            1031069,
        ]
    ],
    [
        'TITLE'        => 'Когтеточки для кошек',
        'DETAIL_LINK'  => '/catalog/koshki/kogtetochki/',
        'IMAGE'        => '/upload/summer-sale/scrapers.jpg',
        'IMAGE_MOBILE' => '/upload/summer-sale/scrapers--small.jpg',
        'OFFERS'       => [
            1024102,
            1026910,
            1027000,
            1009533,
            1027002,
            1024135,
            1026911,
            1026915,
        ]
    ],
    [
        'TITLE'        => 'Игрушки для собак',
        'DETAIL_LINK'  => '/catalog/sobaki/igrushki/',
        'IMAGE'        => '/upload/summer-sale/dog-toys.jpg',
        'IMAGE_MOBILE' => '/upload/summer-sale/dog-toys--small.jpg',
        'OFFERS'       => [
            1010947,
            1026144,
            1024095,
            1026147,
            1019083,
            1026141,
            1019606,
            1017176,
        ]
    ],
    [
        'TITLE'        => 'Игрушки для кошек',
        'DETAIL_LINK'  => '/catalog/koshki/igrushki-koshki/',
        'IMAGE'        => '/upload/summer-sale/cat-toys.jpg',
        'IMAGE_MOBILE' => '/upload/summer-sale/cat-toys--small.jpg',
        'OFFERS'       => [
            1027357,
            1012388,
            1022576,
            1017124,
            1006986,
            1019063,
            1018875,
            1026846,
        ]
    ],
    [
        'TITLE'        => 'Одежда для собак',
        'DETAIL_LINK'  => 'https://fashion.4lapy.ru/',
        'IMAGE'        => '/upload/summer-sale/dog-clothes.jpg?v=1',
        'IMAGE_MOBILE' => '/upload/summer-sale/dog-clothes--small.jpg?v=1',
        'OFFERS'       => [
            1020983,
            1025296,
            1030937,
            1025175,
            1030877,
            1030943,
            1025681,
            1025784,
        ]
    ]
];
?>

    <style>
        .b-container--new-collection-bags .b-title.b-title--sm {
            font-size: 20px;
        }

        .b-container--new-collection-bags .b-link--title {
            flex-shrink: 0;
            align-self: flex-start;
        }

        @media (max-width: 767px) {
            .b-container--new-collection-bags .b-link--title {
                margin-top: 4px;
            }
        }
    </style>

    <div class="b-container b-container--news">
        <div class="b-news">
            <h1 class="b-title b-title--h1">
                Распродажа до -50% на 2000 товаров
            </h1>

            <div class="b-container b-container--new-collection-bags">
                <?php foreach ($offerData as $offerDatum) { ?>
                    <?php
                    $offerIDs = $offerDatum['OFFERS'];
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

                    <section class="b-common-section">
                        <div class="b-common-section__title-box">
                            <h2 class="b-title b-title--sm"><?= $offerDatum['TITLE'] ?></h2>

                            <a class="b-link b-link--title b-link--title" href="<?= $offerDatum['DETAIL_LINK'] ?>" title="Показать все" target="_blank">
                                <span class="b-link__text b-link__text--title">Показать все</span>
                                <span class="b-link__mobile b-link__mobile--title">Все</span>

                                <span class="b-icon">
                <svg class="b-icon__svg" viewBox="0 0 6 10" width="6px" height="10px">
                  <use class="b-icon__use" xlink:href="/static/build/icons.svg#icon-arrow-right"></use>
                </svg>
              </span>
                            </a>
                        </div>

                        <div class="b-bags-banner">
                            <div class="b-bags-banner__descr">&nbsp;</div>

                            <div class="b-bags-banner__img-wrap b-bags-banner__img-wrap--desktop">
                                <a href="<?= $offerDatum['DETAIL_LINK'] ?>" target="_blank">
                                    <img src="<?= $offerDatum['IMAGE'] ?>">
                                </a>
                            </div>

                            <div class="b-bags-banner__img-wrap b-bags-banner__img-wrap--mobile">
                                <a href="<?= $offerDatum['DETAIL_LINK'] ?>" target="_blank">
                                    <img src="<?= $offerDatum['IMAGE_MOBILE'] ?>">
                                </a>
                            </div>
                        </div>

                        <div class="b-common-wrapper b-common-wrapper--visible js-catalog-wrapper">
                            <?php
                            foreach ($productCollection as $product) {
                                $offers = $product->getOffers();
                                $filteredOffers = $offers->filter(function ($offer) use ($offerIDs) {
                                    /** @var Offer $offer */
                                    return in_array($offer->getXmlId(), $offerIDs);
                                });

                                $APPLICATION->IncludeComponent(
                                    'fourpaws:catalog.element.snippet',
                                    'action',
                                    [
                                        'PRODUCT'      => $product,
                                        'CURRENT_OFFER' => $filteredOffers->current()
                                    ],
                                    null,
                                    [
                                        'HIDE_ICONS' => 'Y'
                                    ]
                                );

                            }
                            ?>
                        </div>
                    </section>
                <?php } ?>
            </div>
        </div>
    </div>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
