<?php

namespace FourPaws\Helpers;

use FourPaws\Helpers\Table\FormTable;
use RuntimeException;

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
     *
     * @throws RuntimeException
     */
    public static function getIdByCode(string $code): int
    {
        $id = 0;

        if ($code) {
            /**
             * @var array $form
             */
            $form = FormTable::query()
                             ->setSelect(['ID'])
                             ->setFilter(['SID' => $code])
                             ->setCacheTtl(360000)
                             ->exec()
                             ->fetch();

            if ($form) {
                $id = $form['ID'];
            }
        }

        if ($id === 0) {
            throw new RuntimeException(\sprintf(
                'Form with code %s is not found',
                $code
            ));
        }

        return $id;
    }
}
