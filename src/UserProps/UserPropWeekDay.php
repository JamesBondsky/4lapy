<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\UserProps;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField\TypeBase;
use CUserTypeManager;

Loc::loadMessages(__FILE__);


class UserPropWeekDay extends TypeBase
{
    public const USER_TYPE = 'week_day';

    /**
     * @var array
     */
    protected static $days = [];

    /**
     * @return array
     */
    public static function getUserTypeDescription(): array
    {
        return [
            'USER_TYPE_ID' => self::USER_TYPE,
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => Loc::getMessage('UserPropWeekDayMess'),
            'BASE_TYPE' => CUserTypeManager::BASE_TYPE_STRING,
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
    ): ?string {
        global $DB;
        switch (strtolower($DB->type)) {
            case 'oracle':
                return 'integer';
            case 'mssql':
                return 'int';
            case 'mysql':
            default:
                return 'int';
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
        } elseif (\is_array($userField)) {
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
        return self::getSelectHTML($htmlControl['NAME'], $userField['FIELD_NAME']);
    }

    /**
     * @param $userField
     * @param $htmlControl
     * @return string
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
     */
    public static function getEditFormHTML($userField, $htmlControl): string
    {
        $return = '&nbsp;';

        if ($userField['EDIT_IN_LIST'] === 'Y') {
            if ($userField['VALUE_ID'] < 1 && !empty($userField['SETTINGS']['DEFAULT_VALUE'])) {
                $htmlControl['VALUE'] = $userField['SETTINGS']['DEFAULT_VALUE'];
            }
            $return = self::getSelectHTML($userField['FIELD_NAME'], $htmlControl['VALUE']);
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
     */
    public static function getAdminListViewHTML(
        /** @noinspection PhpUnusedParameterInspection */
        $userField,
        $htmlControl
    ): string {
        if (!empty($htmlControl['VALUE'])) {
            return self::getDay($htmlControl['VALUE']);
        }

        return '&nbsp;';
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return mixed
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
     */
    public static function getEditFormHTMLMulty(
        /** @noinspection PhpUnusedParameterInspection */
        $userField,
        $htmlControl
    ): string {
        $return = '&nbsp;';
        if ($userField['EDIT_IN_LIST'] === 'Y') {
            $return = '<table id="table_' . $userField['FIELD_NAME'] . '">
                <tr><td>' . self::getSelectHTML($htmlControl['NAME'], $htmlControl['VALUE'], true) . '</td></tr>
            </table>';
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
     */
    public static function getAdminListViewHTMLMulty(
        /** @noinspection PhpUnusedParameterInspection */
        $userField,
        $htmlControl
    ): string {
        if (!empty($htmlControl['VALUE'])) {
            $arPrint = [];

            if (\is_array($htmlControl['VALUE']) && !empty($htmlControl['VALUE'])) {
                foreach ($htmlControl['VALUE'] as $val) {
                    if (!empty($val)) {
                        $arPrint[] = self::getDay($val);
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
     */
    public static function onSearchIndex($userField): string
    {
        if (\is_array($userField['VALUE'])) {
            return static::getAdminListViewHTMLMulty($userField, ['VALUE' => $userField['VALUE']]);
        }

        return static::getAdminListViewHTML($userField, ['VALUE' => $userField['VALUE']]);
    }

    /**
     * @param int $value
     * @return string
     */
    protected static function getDay(int $value): string
    {
        $dayofweek = date('w', strtotime(new \DateTime()));
        return FormatDate('l', strtotime(($value - $dayofweek) . ' day', strtotime(new \DateTime())));
    }

    /**
     * @param $name
     * @param null $current
     * @param bool $multiple
     * @return string
     */
    protected static function getSelectHTML($name, $current = null, $multiple = false): string
    {
        if (empty(static::$days)) {
            for ($i = 0; $i < 7; $i++) {
                static::$days[$i] = static::getDay($i);
            }
        }
        $return = '<select name="' . $name . '" ' . ($multiple ? 'multiple' : '') . '>';
        $return .= '<option></option>';
        foreach (static::$days as $i => $day) {
            $selected = false;
            if (null !== $current) {
                if (\is_array($current)) {
                    /** @noinspection TypeUnsafeArraySearchInspection */
                    $selected = \in_array($i, $current);
                } else {
                    $selected = $i === (int)$current;
                }
            }

            $return .= '<option value="' . $i . '" ' . ($selected ? 'selected' : '') . '>' . $day . '</option>';
        }
        $return .= '</select>';

        return $return;
    }
}
