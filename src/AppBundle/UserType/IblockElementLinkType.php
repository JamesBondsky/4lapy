<?php

namespace FourPaws\AppBundle\UserType;

use CIBlockSection;
use WebArch\BitrixCache\BitrixCache;
use WebArch\BitrixUserPropertyType\IblockSectionLinkType;

/**
 * Class IblockElementLinkType
 *
 * @package FourPaws\AppBundle\UserType
 */
class IblockElementLinkType extends IblockSectionLinkType
{
    /**
     * @inheritdoc
     */
    public static function getDescription(): string
    {
        return 'Привязка к элементу инфоблока (с окном поиска)';
    }

    /**
     * @inheritdoc
     */
    public static function getEditFormHTML($userField, $htmlControl)
    {
        $spanValue = self::getLinkedSectionFullName($htmlControl['VALUE']);

        //TODO Добавить множественный режим.
        $name = $htmlControl['NAME'];
        $key = 'x1';

        $spanId = 'sp_' . md5($name) . '_' . $key;

        $params = [
            'lang' => LANGUAGE_ID,
            'IBLOCK_ID' => (int)$userField['SETTINGS'][self::SETTING_IBLOCK_ID],
            'n' => $name,
            'k' => $key,
            // 'iblockfix' => 'y',
        ];

        $popupWindowParams = '/bitrix/admin/iblock_element_search.php?' . htmlentities(http_build_query($params));

        $return = <<<END
            <input name="{$htmlControl['NAME']}"
                   id="{$name}[{$key}]"
                   value="{$htmlControl['VALUE']}"
                   size="5"
                   type="text">
            <input value="..." 
               onclick="jsUtils.OpenWindow('{$popupWindowParams}', 900, 700);" 
               type="button">&nbsp;
            <span id="{$spanId}">{$spanValue}</span>
END;

        return $return;
    }

    /**
     * Возвращает полное имя привязанного раздела со всей иерархией.
     *
     * @param int $sectionId
     *
     * @return string
     * @throws Exception
     */
    private static function getLinkedSectionFullName($sectionId)
    {
        $bitrixCache = new BitrixCache();

        $doGetLinkedSectionFullName = function () use ($sectionId, $bitrixCache) {

            if ($sectionId <= 0) {
                $bitrixCache->abortCache();

                return self::LABEL_NO_VALUE;
            }

            $section = CIBlockSection::GetList([], ['=ID' => $sectionId], false, ['IBLOCK_ID'], ['nTopCount' => 1])
                ->Fetch();
            if (false == $section) {
                $bitrixCache->abortCache();

                return self::LABEL_NO_VALUE;
            }

            //TODO Придумать, как заставить работать такое тегирование
            // $bitrixCache->withIblockTag((int)$section['IBLOCK_ID']);

            $path = [];
            $dbChain = CIBlockSection::GetNavChain($section['IBLOCK_ID'], $sectionId, ['NAME']);
            while ($item = $dbChain->Fetch()) {
                $path[] = trim($item['NAME']);
            }

            return implode(' / ', $path);
        };

        $result = $bitrixCache->withId(__METHOD__ . '_' . $sectionId)
            ->resultOf($doGetLinkedSectionFullName);

        return trim($result['result']);
    }
}