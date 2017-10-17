<?php

namespace FourPaws\Catalog;

use Bitrix\Main\Entity\DataManager;
use FourPaws\App\Application;
use FourPaws\BitrixOrm\Model\HlbReferenceItem;
use FourPaws\BitrixOrm\Query\HlbReferenceQuery;
use InvalidArgumentException;

abstract class Utils
{
    /**
     * Возвращает элемент справочника для любого HL-блока. Если элемент не найден или передан пустой код, возвращается
     * пустой элемент справочника, чтобы не повреждать работе каталога.
     *
     * @param string $hlBlockServiceName Название сервиса для этого HL-блока
     * @param string $xmlId
     *
     * @return HlbReferenceItem
     *
     * TODO Добавить такой же метод для множественных значений
     */
    public static function getReference(string $hlBlockServiceName, string $xmlId): HlbReferenceItem
    {
        if (trim($xmlId) == '') {
            return new HlbReferenceItem();
        }

        /** @var DataManager $dataManager */
        $dataManager = Application::getInstance()->getContainer()->get($hlBlockServiceName);

        // if (!($dataManager instanceof DataManager)) {
        //     throw new InvalidArgumentException('Неверное имя сервиса: не является HL-блоком или чем-то совместимым.');
        // }

        $reference = (new HlbReferenceQuery($dataManager::query()))
            ->withFilter(['=UF_XML_ID' => $xmlId])
            ->exec()
            ->current();

        if ($reference instanceof HlbReferenceItem) {
            return $reference;
        }

        return new HlbReferenceItem();
    }
}
