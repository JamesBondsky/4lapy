<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

class Delivery_dpd_replace_event_handler20171220180153 extends SprintMigrationBase
{
    protected $description = 'Заменяет обработчик модуля ipol.dpd';

    const MODULE_ID = 'ipol.dpd';

    protected $oldHandler = [
        'module'   => 'sale',
        'name'     => 'onSaleDeliveryHandlersBuildList',
        'callback' => ['\\Ipolh\\DPD\\Delivery\\DPD', 'Init'],
        'sort'     => 100,
        'path'     => '',
        'args'     => [],
    ];

    protected $newHandler = [
        'module'   => 'sale',
        'name'     => 'onSaleDeliveryHandlersBuildList',
        'callback' => ['\\FourPaws\\DeliveryBundle\\Dpd\\Calculator', 'Init'],
        'sort'     => 100,
        'path'     => '',
        'args'     => [],
    ];

    public function up()
    {
        if (!Loader::includeModule(self::MODULE_ID)) {
            $this->log()->error('Модуль ' . self::MODULE_ID . ' не установлен');

            return false;
        }

        EventManager::getInstance()->unRegisterEventHandler(
            $this->oldHandler['module'],
            $this->oldHandler['name'],
            self::MODULE_ID,
            $this->oldHandler['callback'] ? $this->oldHandler['callback'][0] : '',
            $this->oldHandler['callback'] ? $this->oldHandler['callback'][1] : '',
            $this->oldHandler['sort'] ?: 100,
            $this->oldHandler['path'] ?: '',
            $this->oldHandler['args'] ?: []
        );
        $this->log()->info('Удален стандартный обработчик модуля ' . self::MODULE_ID);

        EventManager::getInstance()->registerEventHandler(
            $this->newHandler['module'],
            $this->newHandler['name'],
            self::MODULE_ID,
            $this->newHandler['callback'] ? $this->newHandler['callback'][0] : '',
            $this->newHandler['callback'] ? $this->newHandler['callback'][1] : '',
            $this->newHandler['sort'] ?: 100,
            $this->newHandler['path'] ?: '',
            $this->newHandler['args'] ?: []
        );
        $this->log()->info('Подключен новый обработчик для модуля ' . self::MODULE_ID);

        return true;
    }

    public function down()
    {
        EventManager::getInstance()->unRegisterEventHandler(
            $this->newHandler['module'],
            $this->newHandler['name'],
            self::MODULE_ID,
            $this->newHandler['callback'] ? $this->newHandler['callback'][0] : '',
            $this->newHandler['callback'] ? $this->newHandler['callback'][1] : '',
            $this->newHandler['sort'] ?: 100,
            $this->newHandler['path'] ?: '',
            $this->newHandler['args'] ?: []
        );
        $this->log()->info('Удален новый обработчик для модуля ' . self::MODULE_ID);

        if (!Loader::includeModule(self::MODULE_ID)) {
            $this->log()->error('Модуль ' . self::MODULE_ID . ' не установлен');

            return false;
        }

        EventManager::getInstance()->registerEventHandler(
            $this->oldHandler['module'],
            $this->oldHandler['name'],
            self::MODULE_ID,
            $this->oldHandler['callback'] ? $this->oldHandler['callback'][0] : '',
            $this->oldHandler['callback'] ? $this->oldHandler['callback'][1] : '',
            $this->oldHandler['sort'] ?: 100,
            $this->oldHandler['path'] ?: '',
            $this->oldHandler['args'] ?: []
        );
        $this->log()->info('Подключен стандартный обработчик модуля ' . self::MODULE_ID);
    }
}
