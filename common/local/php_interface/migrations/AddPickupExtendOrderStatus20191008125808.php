<?php

namespace Sprint\Migration;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\StatusTable;
use Bitrix\Sale\StatusLangTable;

class AddPickupExtendOrderStatus20191008125808 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Добавляет статут заказа "продление хранения"';

    protected const STATUS_ID = 'X';

    protected const STATUS_DATA = [
        'ID' => self::STATUS_ID,
        'TYPE' => 'O',
        'SORT' => '500',
        'NOTIFY' => 'Y',
        'COLOR' => 'Y',
    ];

    protected const STATUS_LANG_DATA = [
        'STATUS_ID' => self::STATUS_ID,
        'LID' => 'ru',
        'NAME' => 'Продлить хранение',
        'DESCRIPTION' => 'Продлить хранение',
    ];

    public function up(): bool
    {
        try {
            if ($this->addStatus()) {
                if ($this->addLangStatus()) {
                    return true;
                }

                $this->deleteStatus();
                return false;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function down(): bool
    {
        try {
            if ($this->deleteLangStatus()) {
                if ($this->deleteStatus()) {
                    return true;
                }

                $this->addLangStatus();
                return false;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function addStatus(): bool
    {
        try {
            if (StatusTable::add(self::STATUS_DATA)) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function deleteStatus(): bool
    {
        $res = StatusTable::query()->setSelect(['ID', 'SORT'])->setFilter(['=ID' => static::STATUS_ID])->exec();

        if ($res) {
            try {
                if (StatusTable::delete(static::STATUS_ID)) {
                    return true;
                }

                return false;
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    protected function addLangStatus(): bool
    {
        try {
            if (StatusLangTable::add(self::STATUS_LANG_DATA)) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function deleteLangStatus(): bool
    {
        $res = StatusLangTable::query()->setSelect(['ID', 'SORT'])->setFilter(['=ID' => static::STATUS_ID])->exec();

        if ($res) {
            try {
                if (StatusLangTable::delete(static::STATUS_ID)) {
                    return true;
                }

                return false;
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }
}
