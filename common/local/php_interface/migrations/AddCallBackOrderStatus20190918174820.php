<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\StatusTable;
use Bitrix\Sale\StatusLangTable;

class AddCallBackOrderStatus20190918174820 extends SprintMigrationBase
{
    protected $description = 'Добавляет статут заказа "перезвонить"';

    protected const STATUS_ID = 'B';

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
        'NAME' => 'Перезвонить',
        'DESCRIPTION' => 'Перезвонить',
    ];

    public function up()
    {
        if ($this->addStatus()) {
            if ($this->addLangStatus()) {
                return true;
            } else {
                $this->deleteStatus();
                return false;
            }
        } else {
            return false;
        }
    }

    public function down()
    {
        if ($this->deleteLangStatus()) {
            if ($this->deleteStatus()) {
                return true;
            } else {
                $this->addLangStatus();
                return false;
            }
        } else {
            return false;
        }
    }

    protected function addStatus()
    {
        try {
            if (StatusTable::add(self::STATUS_DATA)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function deleteStatus()
    {
        $res = StatusTable::query()->setSelect(['ID', 'SORT'])->setFilter(['=ID' => static::STATUS_ID])->exec();

        if ($res) {
            try {
                if (StatusTable::delete(static::STATUS_ID)) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    protected function addLangStatus()
    {
        try {
            if (StatusLangTable::add(self::STATUS_LANG_DATA)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function deleteLangStatus()
    {
        $res = StatusLangTable::query()->setSelect(['ID', 'SORT'])->setFilter(['=ID' => static::STATUS_ID])->exec();

        if ($res) {
            try {
                if (StatusLangTable::delete(static::STATUS_ID)) {
                    return true;
                } else {
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }
}
