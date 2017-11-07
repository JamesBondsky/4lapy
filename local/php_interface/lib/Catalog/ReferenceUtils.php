<?php

namespace FourPaws\Catalog;

use Bitrix\Main\DB\ArrayResult;
use Bitrix\Main\Entity\DataManager;
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Collection\HlbReferenceItemCollection;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Query\HlbReferenceQuery;

abstract class ReferenceUtils
{
    /**
     * Возвращает элемент справочника для любого HL-блока. Если элемент не найден или передан пустой код, возвращается
     * пустой элемент справочника, чтобы не повреждать работе каталога.
     *
     * @param string $hlBlockServiceName Название сервиса для этого HL-блока
     * @param string $xmlId
     *
     * @return HlbReferenceItem
     */
    public static function getReference(string $hlBlockServiceName, string $xmlId): HlbReferenceItem
    {
        if (trim($xmlId) == '') {
            return new HlbReferenceItem();
        }

        /** @var DataManager $dataManager */
        $dataManager = Application::getInstance()->getContainer()->get($hlBlockServiceName);
        $reference = (new HlbReferenceQuery($dataManager::query()))
            ->withFilter(['=UF_XML_ID' => $xmlId])
            ->exec()
            ->current();

        if ($reference instanceof HlbReferenceItem) {
            return $reference;
        }

        return new HlbReferenceItem();
    }

    /**
     * @param string $hlBlockServiceName Название сервиса для этого HL-блока
     * @param array $xmlIdList
     *
     * @return HlbReferenceItemCollection
     */
    public static function getReferenceMulti(string $hlBlockServiceName, array $xmlIdList): HlbReferenceItemCollection
    {
        $xmlIdList = array_filter(
            $xmlIdList,
            function ($xmlId) {
                return trim($xmlId) != '';
            }
        );

        if (empty($xmlIdList)) {
            //Пустая коллекция
            return new HlbReferenceItemCollection(new ArrayResult([]));
        }

        /** @var DataManager $dataManager */
        $dataManager = Application::getInstance()->getContainer()->get($hlBlockServiceName);

        /** @var HlbReferenceItemCollection $collectionBase */
        $collectionBase = (new HlbReferenceQuery($dataManager::query()))
            ->withFilter(['=UF_XML_ID' => $xmlIdList])
            ->exec();

        return $collectionBase;
    }
}
