<?php

namespace FourPaws\MobileApiBundle\Services;


use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Application;

class PushEventAgent
{
    use LazyLoggerAwareTrait;

    /** @var PushEventAgent $instance */
    protected static $instance;

    private function __construct() {}
    private function __clone() {}

    /**
     * @return PushEventAgent
     */
    public static function getInstance()
    {
        if(is_null(static::$instance)) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Добавляет push-сообщения в очередь на отправку (добавление в табличку push_event)
     */
    public static function addPushMessagesToQueue()
    {
        try {
            $pushEventService = Application::getInstance()->getContainer()->get('push_event.service');
            /** @var $pushEventService PushEventService */
            // $pushEventService->handleRowsWithFile();
            $pushEventService->handleRowsWithoutFile();
        }
        catch (\Exception $exception)
        {
            $instance = static::getInstance();
            $instance->log()->critical(
                sprintf(
                    '%s exception: %s',
                    __METHOD__,
                    $exception->getMessage()
                )
            );
        }

        return '\\' . __METHOD__ . '();';
    }

    /**
     * Отправляет push-сообщения на android
     */
    public static function execPushEventsForAndroid()
    {
        try {
            $pushEventService = Application::getInstance()->getContainer()->get('push_event.service');
            /** @var $pushEventService PushEventService */
            $pushEventService->execPushEventsForAndroid();
        }
        catch (\Exception $exception)
        {
            $instance = static::getInstance();
            $instance->log()->critical(
                sprintf(
                    '%s exception: %s',
                    __METHOD__,
                    $exception->getMessage()
                )
            );
        }
        return '\\' . __METHOD__ . '();';
    }

    /**
     * Отправляет push-сообщения на ios
     */
    public static function execPushEventsForIos()
    {
        try {
            $pushEventService = Application::getInstance()->getContainer()->get('push_event.service');
            /** @var $pushEventService PushEventService */
            $pushEventService->execPushEventsForIos();
        }
        catch (\Exception $exception)
        {
            $instance = static::getInstance();
            $instance->log()->critical(
                sprintf(
                    '%s exception: %s',
                    __METHOD__,
                    $exception->getMessage()
                )
            );
        }

        /*
        $localCertPath = "{$_SERVER['DOCUMENT_ROOT']}/local/backend/push_certs/lapy_prod_merge.pem";

        $oServer = new \ApnsPHP_Push_Server(\ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION, $localCertPath);

        $oServer->setLogger(new \ApnsPHP_Log_Embedded());
        $oServer->getLogger()->lockSend();
        $oServer->setProviderCertificatePassphrase('lapy');
        $oServer->setProcesses(\Lapy\Push\EventTable::PROCESS_SEND);
        $oServer->start();

        // выбираем push'и необходимые к отправке
        $oDateTime = new \Bitrix\Main\Type\DateTime();

        $oEvents = \Lapy\Push\EventTable::getList(array(
            'filter' => array(
                '=PLATFORM' => \Lapy\Push\EventTable::PLATFORM_IOS,
                '<=DATE_TIME_EXEC' => $oDateTime,
                '=SUCCESS_EXEC' => \Lapy\Push\EventTable::STATUS_ID_WAIT
            ),
            'select' => array('ID', 'TOKEN', 'MESSAGE')
        ));

        while ($arEvent = $oEvents->fetch()) {
            try {
                $oMessage = new \ApnsPHP_Message($arEvent['TOKEN']);
                $oMessage->setBadge(1);
                $oMessage->setSound();
                $oMessage->setText($arEvent['MESSAGE']['TITLE']);
                $oMessage->setCustomProperty('type', $arEvent['MESSAGE']['TYPE']);
                $oMessage->setCustomProperty('id', $arEvent['MESSAGE']['ID']);

                $oServer->add($oMessage);

                // делаем задержку между отправками
                usleep(\Lapy\Push\EventTable::DELAY_SEND);

                \Lapy\Push\EventTable::update($arEvent['ID'], array(
                    'SUCCESS_EXEC' => \Lapy\Push\EventTable::STATUS_ID_OK
                ));
            } catch (Exception $e) {
                \Lapy\Push\EventTable::update($arEvent['ID'], array(
                    'SUCCESS_EXEC' => \Lapy\Push\EventTable::STATUS_ID_ERROR
                ));
            }
        }

        // ждём доотправки очереди
        // usleep(5 * 1000000);
        usleep(15 * 1000000);
        */
        return '\\' . __METHOD__ . '();';
    }
}
