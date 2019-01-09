<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services;


use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\AppBundle\Enum\CrudGroups;
use FourPaws\External\ApplePushNotificationService;
use FourPaws\External\FireBaseCloudMessagingService;
use FourPaws\MobileApiBundle\Entity\ApiPushEvent;
use FourPaws\MobileApiBundle\Dto\Object\PushMessage;
use FourPaws\MobileApiBundle\Entity\ApiUserSession;
use FourPaws\MobileApiBundle\Repository\ApiPushEventRepository;
use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;
use FourPaws\MobileApiBundle\Tables\ApiPushEventTable;
use FourPaws\MobileApiBundle\Tables\ApiUserSessionTable;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;

class PushEventService
{

    /**
     * @var ArrayTransformerInterface
     */
    private $transformer;

    /**
     * @var ApiUserSessionRepository
     */
    private $apiUserSessionRepository;

    /**
     * @var ApiPushEventRepository
     */
    private $apiPushEventRepository;

    /**
     * @var FireBaseCloudMessagingService
     */
    private $fireBaseCloudMessagingService;

    /**
     * @var ApplePushNotificationService
     */
    private $applePushNotificationService;


    public function __construct(
        ArrayTransformerInterface $transformer,
        ApiUserSessionRepository $apiUserSessionRepository,
        ApiPushEventRepository $apiPushEventRepository,
        FireBaseCloudMessagingService $fireBaseCloudMessagingService,
        ApplePushNotificationService $applePushNotificationService
    )
    {
        $this->transformer = $transformer;
        $this->apiUserSessionRepository = $apiUserSessionRepository;
        $this->apiPushEventRepository = $apiPushEventRepository;
        $this->fireBaseCloudMessagingService = $fireBaseCloudMessagingService;
        $this->applePushNotificationService = $applePushNotificationService;
    }

    /**
     * Обработка записей в ИБ с привязками файлов
     */
    public function handleRowsWithFile()
    {
        // toDo
        // выбираем из ИБ элементы push'ей с привязанными файлами
        /*
        $oElements = \CIBlockElement::GetList(
            array(),
            array(
                'IBLOCK_ID' => \CIBlockTools::GetIBlockId('push_notification'),
                'ACTIVE' => 'Y',
                '!PROPERTY_FILE' => false,
            ),
            false,
            false,
            array('ID')
        );

        while ($arPushElem = $oElements->Fetch()) {
            try {
                $oPushElement = \Lapy\Push\PushElement::load($arPushElem['ID']);
            } catch (\Exception $e) {
                // что то пошло не так
                // деактивируем элемент push'а
                $oIbElement = new \CIBlockElement();
                $oIbElement->Update($arPushElem['ID'], array('ACTIVE' => 'N'));
                unset($oIbElement);
                continue;
            }

            if (!is_object($oPushElement)) {
                continue;
            }

            // обрабатываем файл с номерами телефонов
            $oPushElement->parseFile();

            // деактивируем элемент push'а и удаляем привязку к файлу
            $oPushElement->setFields(array(
                'ACTIVE' => 'N',
                'FILE' => false,
            ));
            $oPushElement->save();
        }
        */
    }

    /**
     * Обработка записей в ИБ без привязки файлов
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     */
    public function handleRowsWithoutFile()
    {
        // выбираем push сообщения за указанный период
        $hlBlockPushMessages = Application::getHlBlockDataManager('bx.hlblock.pushmessages');
        $res = $hlBlockPushMessages->query()
            ->setFilter([
                'UF_ACTIVE' => true,
                '<=UF_START_SEND' => (new \Bitrix\Main\Type\DateTime())->add('6 hour')->format('d.m.Y H:i:s'),
            ])
            ->setSelect([
                '*'
            ])
            ->exec();

        /** @var PushMessage[] $pushMessages */
        $pushMessages = $this->transformer->fromArray(
            $res->fetchAll(),
            'array<' . PushMessage::class . '>'
        );

        foreach ($pushMessages as $pushMessage) {
            $sessions = $this->findUsersSessions($pushMessage);
            if (!empty($sessions)) {
                foreach ($sessions as $session) {
                    $pushEvent = $this->convertToPushEvent($pushMessage, $session);
                    $this->apiPushEventRepository->create($pushEvent);
                }
            }
            // деактивируем push-сообщение
            $hlBlockPushMessages::update($pushMessage->getId(), [
                'UF_ACTIVE' => false,
            ]);
        }
    }


    /**
     * @throws \ApnsPHP_Message_Exception
     */
    public function execPushEventsForAndroid()
    {
        $pushEvents = $this->apiPushEventRepository->findForAndroid();
        foreach ($pushEvents as $pushEvent) {
            $this->applePushNotificationService->sendNotification(
                $pushEvent->getPushToken(),
                $pushEvent->getMessageText(),
                $pushEvent->getMessageId(),
                $pushEvent->getMessageType()
            );
            $pushEvent->setSuccessExec(ApiPushEvent::EXEC_SUCCESS_CODE);
            $this->apiPushEventRepository->update($pushEvent);
        }
    }

    public function execPushEventsForIos()
    {
        $pushEvents = $this->apiPushEventRepository->findForIos();
        foreach ($pushEvents as $pushEvent) {
            $response = $this->fireBaseCloudMessagingService->sendNotification(
                $pushEvent->getPushToken(),
                $pushEvent->getMessageText(),
                $pushEvent->getMessageId(),
                $pushEvent->getMessageType()
            );
            $execCode = $response->getStatusCode() === 200 ? ApiPushEvent::EXEC_SUCCESS_CODE : ApiPushEvent::EXEC_FAIL_CODE;
            $pushEvent->setSuccessExec($execCode);
            $this->apiPushEventRepository->update($pushEvent);
        }
    }

    /**
     * Ищет сессии пользователей для конкретного push-сообщения
     * @param PushMessage $pushMessage
     * @return array|ApiUserSession[]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function findUsersSessions(PushMessage $pushMessage)
    {
        // Выбираем только тех пользователей, у которых заведен push token
        $findBy = [
            '!PUSH_TOKEN' => false,
        ];

        // если в push-сообщении указаны конкретные id пользователей - ищем пользователя по этим id
        $userIds = [];
        foreach ($pushMessage->getUsers() as $user) {
            $userIds[] = $user->getId();
        }
        $userFilter = [
            'LOGIC' => 'OR',
        ];
        if (!empty($userIds)) {
            $userFilter[] =[
                '=USER_ID' => $userIds,
            ];
        }

        // если в push-сообщении указаны группы - выбираем пользователей по группам (UF_полям)
        if (!empty($pushMessage->getGroupEntity())) {
            $userGroupFilter['LOGIC'] = 'OR';
            foreach ($pushMessage->getGroupEntity() as $groupEntity) {
                $userGroupFilter[] = [
                    'USER.' . $groupEntity->getXmlId() => true,
                ];
            }
            $userFilter[] = $userGroupFilter;
        }

        if (empty($userFilter)) {
            // если адресаты не указаны - ничего отправлять не нужно
            return [];
        }

        if ($pushMessage->getPlatformId()) {
            // если указана платформа - фильтруем пользователей еще и по платформе (ios / android)
            $findBy['PLATFORM'] = substr($pushMessage->getPlatformEntity()->getXmlId(), 0);
        }

        $findBy[] = $userFilter;

        $sessions = $this->apiUserSessionRepository->findBy($findBy);

        return $sessions;
    }

    /**
     * @param PushMessage $pushMessage
     * @param ApiUserSession $session
     * @return ApiPushEvent
     */
    protected function convertToPushEvent(PushMessage $pushMessage, ApiUserSession $session)
    {
        return (new ApiPushEvent())
            ->setPlatform($session->getPlatform())
            ->setPushToken($session->getPushToken())
            ->setMessageId($pushMessage->getId())
            ->setDateTimeExec($pushMessage->getStartSend());
    }
}
