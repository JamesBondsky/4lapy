<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Helpers;

use Bitrix\Iblock\ElementTable;

/**
 * Class IblockHelper
 *
 * @package FourPaws\Helpers
 */
class IblockHelper
{
    /**
     * @param int $iBlockId
     * @param string $name
     * @param string $code
     *
     * @return string
     */
    public static function generateUniqueCode(int $iBlockId, string $name, string $code): string
    {
        $i = 0;
        while ($i < 10) {
            $tmpCode = $i > 0 ? $code . $i : $code;
            $r = ElementTable::query()
                ->setSelect(['ID'])
                ->addFilter('IBLOCK_ID', $iBlockId)
                ->addFilter('=CODE', $tmpCode)
                ->setLimit(1)
                ->exec()
                ->getSelectedRowsCount();

            if ($r) {
                $i++;
                continue;
            }

            return $tmpCode;
        }

        return \md5($code . \microtime());
    }
}
