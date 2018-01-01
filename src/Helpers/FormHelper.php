<?php

namespace FourPaws\Helpers;

use FourPaws\Helpers\Table\FormTable;

/**
 * Class FormHelper
 *
 * @package FourPaws\Helpers
 */
class FormHelper
{
    /**
     * @param string $code
     *
     * @return int
     */
    public static function getIdByCode(string $code) : int
    {
        return !empty($code) ? (int)FormTable::query()->setSelect(['ID'])->setFilter(['SID' => $code])->exec()->fetch(
        )['ID'] : 0;
    }
}