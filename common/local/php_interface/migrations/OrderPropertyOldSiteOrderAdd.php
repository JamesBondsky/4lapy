<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\OrderPropsTable;
use Exception;


/**
 * Class OrderPropertyOldSiteOrderAdd
 *
 * @package Sprint\Migration
 */
class OrderPropertyOldSiteOrderAdd extends SprintMigrationBase
{

    protected const PROP_CODE = 'IS_OLD_SITE_ORDER';

    protected $description = 'Добавление свойства "Заказ со старого сайта"';

    /**
     * @return bool
     */
    public function up()
    {
        try {
            $prop = OrderPropsTable::getList(
                [
                    'filter' => [
                        'CODE' => self::PROP_CODE,
                    ],
                ]
            )->fetch();

            if (!$prop) {
                $addResult = OrderPropsTable::add(
                    [
                        'CODE'           => self::PROP_CODE,
                        'NAME'           => 'Заказ со старого сайта',
                        'TYPE'           => 'Y/N',
                        'REQUIRED'       => 'N',
                        'USER_PROPS'     => 'N',
                        'DESCRIPTION'    => '',
                        'PERSON_TYPE_ID' => 1,
                        'PROPS_GROUP_ID' => 4,
                        'UTIL'           => 'Y',
                        'IS_FILTERED'    => 'Y',
                        'SORT'           => '5100',
                    ]
                );
                if (!$addResult->isSuccess()) {
                    $this->log()->error('Ошибка при добавлении свойства заказа ' . self::PROP_CODE);

                    return false;
                }
            } else {
                $this->log()->warning('Свойство заказа ' . self::PROP_CODE . ' уже существует');
            }
        } catch (ObjectPropertyException | ArgumentException | SystemException | Exception $e) {

            $this->log()->error(sprintf('Ошибка добавления свойства заказа %s: %s', self::PROP_CODE, $e->getMessage()));
        }

    }

    /**
     * @return bool|void
     */
    public function down()
    {
        /**
         * Do not required
         */
    }

}
