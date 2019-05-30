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
class OrderPropertyIsNewSiteOrder20190227141639 extends SprintMigrationBase
{

    protected const PROP_CODE = 'IS_NEW_SITE_ORDER';
    protected const PROP_NAME = 'Создан на новом сайте';

    protected $description = 'Добавление свойства "' . self::PROP_NAME . '"';

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
                        'CODE' => self::PROP_CODE,
                        'NAME' => self::PROP_NAME,
                        'TYPE' => 'Y/N',
                        'REQUIRED' => 'N',
                        'USER_PROPS' => 'N',
                        'DESCRIPTION' => '',
                        'PERSON_TYPE_ID' => 1,
                        'PROPS_GROUP_ID' => 4,
                        'UTIL' => 'Y',
                        'IS_FILTERED' => 'Y',
                        'SORT' => '9000',
                        'DEFAULT_VALUE' => 'Y',
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

            return false;
        }

        return true;;
    }
}
