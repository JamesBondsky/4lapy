<?php

namespace FourPaws\BitrixOrm\Utils;

use Bitrix\Main\DB\ArrayResult;
use Bitrix\Main\Entity\DataManager;
use FourPaws\BitrixOrm\Collection\HlbReferenceItemCollection;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Query\HlbReferenceQuery;

/**
 * Class ReferenceUtils
 *
 * Облегчение работы с HL-инфоблоками в режиме справочников.
 *
 * @package FourPaws\BitrixOrm\Utils
 */
abstract class ReferenceUtils
{
    /**
     * @param DataManager $dataManager
     * @param string $xmlId
     *
     * @return HlbReferenceItem
     */
    public static function getReference(DataManager $dataManager, string $xmlId): HlbReferenceItem
    {
        if (\trim($xmlId) === '') {
            return new HlbReferenceItem();
        }

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
     * @param DataManager $dataManager
     * @param array $xmlIdList
     *
     * @return HlbReferenceItemCollection
     */
    public static function getReferenceMulti(DataManager $dataManager, array $xmlIdList): HlbReferenceItemCollection
    {
        $xmlIdList = \array_filter(
            $xmlIdList,
            function ($xmlId) {
                return \trim($xmlId) !== '';
            }
        );

        if (empty($xmlIdList)) {
            //Пустая коллекция
            return new HlbReferenceItemCollection(new ArrayResult([]));
        }

        /** @var HlbReferenceItemCollection $collectionBase */
        $collectionBase = (new HlbReferenceQuery($dataManager::query()))
            ->withFilter(['=UF_XML_ID' => $xmlIdList])
            ->exec();

        return $collectionBase;
    }
}
