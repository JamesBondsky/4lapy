<?php

namespace FourPaws\UserProps;

use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField\TypeBase;
use CUserTypeManager;

Loc::loadMessages(__FILE__);

class UserPropStoreList extends TypeBase
{
    const USER_TYPE = 'catalog_store_list';

    /**
     * @var array
     */
    protected static $stores = [];

    /**
     * @return array
     */
    public static function getUserTypeDescription(): array
    {
        return [
            'USER_TYPE_ID' => self::USER_TYPE,
            'CLASS_NAME'   => __CLASS__,
            'DESCRIPTION'  => Loc::getMessage('UserPropStoreListMess'),
            'BASE_TYPE'    => CUserTypeManager::BASE_TYPE_STRING,
        ];
    }

    /**
     * Return internal type for storing url_preview user type values
     *
     * @param array $userField Array containing parameters of the user field.
     *
     * @return string
     */
    public static function getDBColumnType(
        /** @noinspection PhpUnusedParameterInspection */
        $userField
    ) {
        global $DB;
        switch (strtolower($DB->type)) {
            case 'oracle':
                return 'varchar(20 char)';
            case 'mssql':
                return 'varchar(20)';
            case 'mysql':
            default:
                return 'varchar(20)';
        }
    }

    /**
     * @param array $userField
     *
     * @return array
     */
    public static function prepareSettings(
        /** @noinspection PhpUnusedParameterInspection */
        $userField
    ): array {
        return [
            'DEFAULT_VALUE' => (int)$userField['SETTINGS']['DEFAULT_VALUE']
            > 0 ? (int)$userField['SETTINGS']['DEFAULT_VALUE'] : '',
        ];
    }

    /**
     * @param array $userField Array containing parameters of the user field.
     * @param       $htmlControl
     * @param       $varsFromForm
     *
     * @return string
     */
    public static function getSettingsHTML(
        /** @noinspection PhpUnusedParameterInspection */
        $userField,
        $htmlControl,
        $varsFromForm
    ): string {
        $result = '';
        $value = '';
        if ($varsFromForm) {
            $value = $GLOBALS[$htmlControl['NAME']]['DEFAULT_VALUE'];
        } elseif (is_array($userField)) {
            $value = $userField['SETTINGS']['DEFAULT_VALUE'];
        }

        $result .= '
        <tr>
            <td>' . GetMessage('USER_TYPE_STRING_DEFAULT_VALUE') . ':</td>
            <td>
                ' . $value . '
            </td>
        </tr>
		';

        return $result;
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     */
    public static function getFilterHTML(
        /** @noinspection PhpUnusedParameterInspection */
        $userField,
        $htmlControl
    ): string {
        $replacedName = str_replace(
            [
                '[',
                ']',
            ],
            '_',
            $htmlControl['NAME']
        );

        return self::getStoreSelectHTML($htmlControl['NAME'], $userField['FIELD_NAME']);
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getAdminListEditHTML($userField, $htmlControl): string
    {
        return static::getEditFormHTML($userField, $htmlControl);
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     * @throws LoaderException
     */
    public static function getEditFormHTML($userField, $htmlControl): string
    {
        $return = '&nbsp;';
        $replacedName = str_replace(
            [
                '[',
                ']',
            ],
            '_',
            $htmlControl['NAME']
        );

        if ($userField['EDIT_IN_LIST'] === 'Y') {
            if ($userField['VALUE_ID'] < 1 && !empty($userField['SETTINGS']['DEFAULT_VALUE'])) {
                $htmlControl['VALUE'] = $userField['SETTINGS']['DEFAULT_VALUE'];
            }
            $return = self::getStoreSelectHTML($userField['FIELD_NAME'], $htmlControl['VALUE']);
        } elseif (!empty($htmlControl['VALUE'])) {
            $return = static::getAdminListViewHTML($userField, $htmlControl);
        }

        return $return;
    }

    /** @noinspection PhpUnusedParameterInspection */

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getAdminListViewHTML(
        /** @noinspection PhpUnusedParameterInspection */
        $userField,
        $htmlControl
    ): string {
        if (!empty($htmlControl['VALUE'])) {
            Loader::includeModule('sale');

            return '[' . $htmlControl['VALUE'] . ']' . self::getStoreByXmlId($htmlControl['VALUE'])['TITLE'];
        }

        return '&nbsp;';
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return mixed
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getAdminListEditHTMLMulty($userField, $htmlControl): string
    {
        return static::getEditFormHTMLMulty($userField, $htmlControl);
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getEditFormHTMLMulty(
        /** @noinspection PhpUnusedParameterInspection */
        $userField,
        $htmlControl
    ): string {
        $return = '&nbsp;';
        if ($userField['EDIT_IN_LIST'] === 'Y') {
            $name = $userField['FIELD_NAME'];
            $return = '<table id="table_' . $name . '">';
            if (is_array($htmlControl['VALUE']) && !empty($htmlControl['VALUE'])) {
                foreach ($htmlControl['VALUE'] as $i => $val) {
                    $return .= '<tr><td>' . self::getStoreSelectHTML($name . '[' . $i . ']', $val) . '</td></tr>';
                }
            }
            $return .= '<tr><td>' . self::getStoreSelectHTML($userField['FIELD_NAME'] . '[]') . '</td></tr>';

            $return .= '
            <tr>
                <td>
                    <input type="button" value="' . GetMessage(
                    "USER_TYPE_PROP_ADD"
                ) . '" onclick="addNewRow(\'table_' . $name . '\', \'' . $name . '[]\')">
                </td>
            </tr>';

            $return .= '</table>';
        } elseif (!empty($htmlControl['VALUE'])) {
            $return = static::getAdminListViewHTMLMulty($userField, $htmlControl);
        }

        return $return;
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public static function getAdminListViewHTMLMulty(
        /** @noinspection PhpUnusedParameterInspection */
        $userField,
        $htmlControl
    ): string {
        if (!empty($htmlControl['VALUE'])) {
            Loader::includeModule('sale');
            $arPrint = [];

            if (is_array($htmlControl['VALUE']) && !empty($htmlControl['VALUE'])) {
                foreach ($htmlControl['VALUE'] as $val) {
                    if (!empty($val)) {
                        $arPrint[] = '[' . $val . ']' . self::getStoreByXmlId($val)['TITLE'];
                    }
                }
            }

            return implode(' / ', $arPrint);
        }

        return '&nbsp;';
    }

    /**
     * @param $userField
     *
     * @return string
     * @throws \Bitrix\Main\LoaderException
     */
    public static function onSearchIndex($userField): string
    {
        if (is_array($userField['VALUE'])) {
            return static::getAdminListViewHTMLMulty($userField, ['VALUE' => $userField['VALUE']]);
        }

        return static::getAdminListViewHTML($userField, ['VALUE' => $userField['VALUE']]);
    }

    /**
     * @return array
     */
    protected static function getStoreList(): array
    {
        if (empty(self::$stores)) {
            $result = [];
            $stores = StoreTable::getList(
                [
                    'filter' => ['ACTIVE' => 'Y'],
                    'select' => ['ID', 'TITLE', 'XML_ID'],
                ]
            );

            while ($store = $stores->fetch()) {
                self::$stores[$store['XML_ID']] = $store;
            }
        }

        return self::$stores;
    }

    /**
     * @param $xmlId
     *
     * @return array
     */
    protected static function getStoreByXmlId($xmlId): array
    {
        if (!self::$stores) {
            self::getStoreList();
        }

        return self::$stores[$xmlId] ?? [];
    }

    /**
     * @param $name
     * @param null $current
     *
     * @return string
     */
    protected static function getStoreSelectHTML($name, $current = null): string
    {
        $stores = self::getStoreList();

        $return = '<select name="' . $name . '">';
        $return .= '<option></option>';
        foreach ($stores as $xmlId => $store) {
            $return .= '<option value="' . $xmlId . '" ' . ($xmlId == $current ? 'selected' : '')
                . '>[' . $xmlId . ']' . $store['TITLE'] . '</option>';
        }
        $return .= '</select>';

        return $return;
    }
}
