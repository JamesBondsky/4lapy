<?php

namespace FourPaws\ProductAutoSort\Controller;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use CIBlockElement;
use CIBlockProperty;
use FourPaws\App\Application;
use FourPaws\App\Model\ResponseContent\JsonContent;
use FourPaws\BitrixOrm\Query\IblockQueryBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class AutosortController
{
    /**
     * @var HLBlockFactory
     */
    protected $hlBlockFactory;

    public function __construct()
    {
        $this->hlBlockFactory = Application::getInstance()->getContainer()->get('bx.hlblock.factory');
    }

    public function propertyHint(int $propertyId)
    {
        $arProperty = CIBlockProperty::GetByID($propertyId)->Fetch();

        if (false === $arProperty) {
            return JsonResponse::create(
                new JsonContent(
                    sprintf('Свойство #%d не существует', $propertyId), false
                )
            );
        }

        /**
         * Для HL-блоков (Справочники)
         */
        if (
            isset(
                $arProperty['USER_TYPE'],
                $arProperty['USER_TYPE_SETTINGS'],
                $arProperty['USER_TYPE_SETTINGS']['TABLE_NAME']
            )
            && 'directory' == $arProperty['USER_TYPE']
            && trim($arProperty['USER_TYPE_SETTINGS']['TABLE_NAME']) != ''
        ) {
            $hintList = $this->getHintForDirectory($arProperty['USER_TYPE_SETTINGS']['TABLE_NAME']);
        }

        /**
         * Для связанных инфоблоков типа "Бренды" и прочее.
         */
        if (
            isset($arProperty['PROPERTY_TYPE'], $arProperty['LINK_IBLOCK_ID'])
            && 'E' == $arProperty['PROPERTY_TYPE']
            && $arProperty['LINK_IBLOCK_ID'] > 0
        ) {
            $hintList = $this->getHintForIblock((int)$arProperty['LINK_IBLOCK_ID']);
        }

        /**
         * Для "Да/Нет"
         */
        if (
            isset($arProperty['USER_TYPE'], $arProperty['PROPERTY_TYPE'])
            && $arProperty['USER_TYPE'] === 'YesNoPropertyType'
            && $arProperty['PROPERTY_TYPE'] === 'N'
        ) {
            $hintList = [
                [
                    'name'  => 'Да',
                    'value' => '1',
                ],
                [
                    'name'  => 'Нет',
                    'value' => '0',
                ],
            ];
        }

        if (!isset($hintList)) {
            return JsonResponse::create(
                new JsonContent(
                    sprintf('Подсказки для этого типа свойства не поддерживаются', $propertyId),
                    false
                )
            );
        }

        if (is_array($hintList) && count($hintList) > 0) {
            array_unshift($hintList, ['name' => '- выберите -', 'value' => '']);
        }

        return JsonResponse::create((new JsonContent())->withData($hintList));
    }

    /**
     * @param string $tableName
     *
     * @return array
     */
    private function getHintForDirectory($tableName)
    {
        $hintList = [];
        $hlBlock = $this->hlBlockFactory::createTableObjectByTable($tableName);

        $result = $hlBlock::query()
                          ->setSelect(['UF_NAME', 'UF_XML_ID'])
                          ->setLimit(1000)
                          ->exec();

        while ($itemFields = $result->fetch()) {
            $hintList[] = [
                'name'  => $itemFields['UF_NAME'],
                'value' => $itemFields['UF_XML_ID'],
            ];
        }

        return $hintList;
    }

    /**
     * @param int $iblockId
     *
     * @return array
     */
    private function getHintForIblock(int $iblockId)
    {
        $hintList = [];

        $elementList = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'NAME' => 'ASC'],
            array_merge(
                ['=IBLOCK_ID' => $iblockId],
                IblockQueryBase::getActiveAccessableElementsFilter()
            ),
            false,
            ['nTopCount' => 1000],
            ['IBLOCK_ID', 'ID', 'NAME']
        );

        while ($element = $elementList->Fetch()) {
            $hintList[] = [
                'name'  => $element['NAME'],
                'value' => $element['ID'],
            ];
        }

        return $hintList;
    }
}
