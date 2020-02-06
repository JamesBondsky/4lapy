<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include.php';

try {
    $app = FourPaws\App\Application::getInstance();
    $container = FourPaws\App\Application::getInstance()
        ->getContainer();

    $s = $container->get(\JMS\Serializer\SerializerInterface::class);
    dump($s->deserialize(file_get_contents('ytf.xml'), FourPaws\CatalogBundle\Dto\Yandex\Feed::class, 'xml'));die;

    $idList = \array_reduce(\Bitrix\Iblock\ElementTable::query()
        //->setCacheTtl(3600)
        ->setSelect(['ID'])
        ->setFilter([
            'IBLOCK_ID'          => \Adv\Bitrixtools\Tools\Iblock\IblockUtils::getIblockId(
                \FourPaws\Enum\IblockType::CATALOG,
                \FourPaws\Enum\IblockCode::PRODUCTS
            ),
            'IBLOCK_SECTION_ID' => [
                0  => 263,
                1  => 642,
                2  => 640,
                3  => 641,
                4  => 639,
                5  => 264,
                6  => 265,
                7  => 266,
                8  => 269,
                9  => 270,
                10 => 271,
                11 => 272,
                12 => 273,
                13 => 13,
                14 => 2,
                15 => 3,
            ],
            'ACTIVE'             => 'Y'
        ])
        ->exec()
        ->fetchAll() ?: [], function ($carry, $on) {
        dump([$carry, $on]);
        $carry[] = $on['ID'];

        return $carry;
    }, []);

    $idList = $idList ?: [-1];
    $filter =
        ['=PROPERTY_CML2_LINK' => $idList,
         '<XML_ID'             => 2000000,
         'ACTIVE'              => 'Y'];

    dump($filter);
    //$feedService = $container->get(YandexFeedService::class);
    // $s = $container->get(\JMS\Serializer\SerializerInterface::class);
    // $class = $s->deserialize(file_get_contents('test.xml'), Feed::class, 'xml');
    //$tr = new BitrixExportConfigTranslator();
    //\dump($tr->translate($tr->getProfileData(2)));
    //echo $s->serialize($class, 'xml');
    /* $registry = $container->get(ConsumerRegistry::class);
    $order = \Bitrix\Sale\Order::load(20321869);
    $registry->consume($order);*/
    /*
        $card = '2600012909469';

        $m = $container->get('manzana.service');
        dump($m->validateCardByNumber($card));
    */

} catch (\Throwable $e) {
    dump($e);
}

