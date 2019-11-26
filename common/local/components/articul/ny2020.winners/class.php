<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

/** @noinspection AutoloadingIssuesInspection */

class Ny20202WinnersComponent extends CBitrixComponent
{
    protected const HL_BLOCK_NAME = 'Ny2020Winners';

    /** @var DataManager */
    protected $dataManager;

    public function onPrepareComponentParams($arParams)
    {
        $arParams['CACHE_TIME'] = $arParams['CACHE_TIME'] ?? 86400;
        return parent::onPrepareComponentParams($arParams);
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function executeComponent()
    {
        if ($this->startResultCache()) {
            $periods = [];

            foreach ($this->getPeriods() as $periodId => $title) {
                $periods[$periodId] = [
                    'title' => $title,
                    'winners' => [],
                ];
            }

            foreach ($this->getWinners() as $winner) {
                $periods[$winner['UF_PERIOD']]['winners'][] = [
                    'name' => $winner['UF_NAME'],
                    'phone' => preg_replace('/^(\d{8})(\d{3})$/', '*(***)****$2', $winner['UF_PHONE']),
                ];
            }

            $this->arResult = [
                'periods' => $periods,
                'totalCount' => count($periods),
            ];

            $this->includeComponentTemplate();
        }
    }

    protected function getPeriods(): array
    {

        try {
            $result = [];
            global $DB;
            $res = $DB->Query(sprintf('select ID, VALUE, XML_ID from b_user_field_enum where USER_FIELD_ID = %s', $this->getPeriodFieldId()));

            while ($a = $res->Fetch()) {
                $result[$a['ID']] = $a['VALUE'];
            }

            return $result;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getPeriodFieldId(): string
    {
        $entity = HighloadBlockTable::query()
            ->setFilter(['NAME' => self::HL_BLOCK_NAME])
            ->setSelect(['ID'])
            ->exec()
            ->fetch();

        global $DB;
        $res = $DB->Query(sprintf('select ID from b_user_field where ENTITY_ID = \'%s\' and FIELD_NAME = \'%s\'', 'HLBLOCK_' . $entity['ID'], 'UF_PERIOD'));

        return (string)($res->Fetch())['ID'];
    }

    protected function getWinners(): array
    {
        try {
            $result = [];
            $res = HLBlockFactory::createTableObject(self::HL_BLOCK_NAME)::query()
                ->setOrder(['UF_SORT' => 'ASC'])
                ->setSelect(['UF_NAME', 'UF_PHONE', 'UF_PERIOD'])
                ->exec();

            while ($winner = $res->fetch()) {
                $result[] = $winner;
            }
            return $result;
        } catch (Exception $e) {
            return [];
        }
    }
}
