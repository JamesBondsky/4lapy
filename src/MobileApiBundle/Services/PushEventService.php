<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Services;


use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Application;
use FourPaws\AppBundle\Enum\CrudGroups;
use FourPaws\External\ApplePushNotificationService;
use FourPaws\External\Exception\FireBaseCloudMessagingException;
use FourPaws\External\FireBaseCloudMessagingService;
use FourPaws\MobileApiBundle\Entity\ApiPushEvent;
use FourPaws\MobileApiBundle\Entity\ApiPushMessage;
use FourPaws\MobileApiBundle\Entity\ApiUserSession;
use FourPaws\MobileApiBundle\Repository\ApiPushEventRepository;
use FourPaws\MobileApiBundle\Repository\ApiUserSessionRepository;
use FourPaws\UserBundle\Repository\UserRepository;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;
use Sly\NotificationPusher\Adapter\Apns;
use Sly\NotificationPusher\Collection\DeviceCollection;
use Sly\NotificationPusher\Exception\AdapterException;
use Sly\NotificationPusher\Model\Device;
use Sly\NotificationPusher\Model\Message;
use Sly\NotificationPusher\Model\Push;
use tests\units\Sly\NotificationPusher\PushManager;

class PushEventService
{

    use LazyLoggerAwareTrait;

    const MAX_PHONES_AMOUNT_PER_REQUEST = 100;

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

    /**
     * @var UserRepository
     */
    private $userRepository;


    public function __construct(
        ArrayTransformerInterface $transformer,
        ApiUserSessionRepository $apiUserSessionRepository,
        ApiPushEventRepository $apiPushEventRepository,
        FireBaseCloudMessagingService $fireBaseCloudMessagingService,
        ApplePushNotificationService $applePushNotificationService,
        UserRepository $userRepository
    )
    {
        $this->transformer = $transformer;
        $this->apiUserSessionRepository = $apiUserSessionRepository;
        $this->apiPushEventRepository = $apiPushEventRepository;
        $this->fireBaseCloudMessagingService = $fireBaseCloudMessagingService;
        $this->applePushNotificationService = $applePushNotificationService;
        $this->userRepository = $userRepository;
    }

    /**
     * Обработка записей с привязками файлов
     * Файл с номерами телефонов парсится,
     * По номерам телефонов определяются пользователи,
     * id пользователей подставляются в поле указанные пользователи
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function handleRowsWithFile()
    {
        // выбираем push с привязанными файлами

        $hlBlockPushMessages = Application::getHlBlockDataManager('bx.hlblock.pushmessages');
        $res = $hlBlockPushMessages->query()
            ->setFilter([
                'UF_ACTIVE' => true,
                '!UF_FILE' => false,
            ])
            ->setSelect([
                '*',
            ])
            ->setLimit(500)
            ->exec();

        $pushMessages = $this->transformer->fromArray(
            $res->fetchAll(),
            'array<' . ApiPushMessage::class . '>'
        );

        /** @var ApiPushMessage $pushMessage */
        foreach ($pushMessages as $pushMessage) {
            $this->parseFile($pushMessage);
            $pushMessage->setFileId(0);

            $data = $this->transformer->toArray(
                $pushMessage,
                SerializationContext::create()->setGroups([CrudGroups::UPDATE])
            );

            $hlBlockPushMessages = Application::getHlBlockDataManager('bx.hlblock.pushmessages');
            $hlBlockPushMessages->update($pushMessage->getId(), $data);
        }
    }

    /**
     * Обработка записей без привязки файлов
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
                'UF_FILE' => false,
            ])
            ->setSelect([
                '*'
            ])
            ->setLimit(500)
            ->exec();

        /** @var ApiPushMessage[] $pushMessages */
        $pushMessages = $this->transformer->fromArray(
            $res->fetchAll(),
            'array<' . ApiPushMessage::class . '>'
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
     * @throws \FourPaws\External\Exception\FireBaseCloudMessagingException
     */
    public function execPushEventsForAndroid()
    {
        $pushEvents = $this->apiPushEventRepository->findForAndroid();
        foreach ($pushEvents as $pushEvent) {
            try {
                $response = $this->fireBaseCloudMessagingService->sendNotification(
                    $pushEvent->getPushToken(),
                    $pushEvent->getMessageText(),
                    $pushEvent->getEventId(),
                    $pushEvent->getMessageTypeEntity()->getXmlId()
                );
                $execCode = $response->getStatusCode() === 200 ? ApiPushEvent::EXEC_SUCCESS_CODE : ApiPushEvent::EXEC_FAIL_CODE;
                $pushEvent->setSuccessExec($execCode);
                $pushEvent->setServiceResponseStatus($response->getStatusCode());
            }
            catch (\Exception $e) {
                $pushEvent->setServiceResponseError($e->getMessage());
            }
            $this->apiPushEventRepository->update($pushEvent);
        }
    }

    /**
     * @throws \ApnsPHP_Exception
     * @throws \ApnsPHP_Push_Server_Exception
     */
    public function execPushEventsForIos1111()
    {
        $this->applePushNotificationService->startServer();
        $pushEvents = $this->apiPushEventRepository->findForIos();
        foreach ($pushEvents as $pushEvent) {
            try {
                $this->applePushNotificationService->sendNotification(
                    $pushEvent->getPushToken(),
                    $pushEvent->getMessageText(),
                    $pushEvent->getEventId(),
                    $pushEvent->getMessageTypeEntity()->getXmlId()
                );

                foreach ($this->applePushNotificationService->getLogMessages() as $logMessage) {
                    $this->log()->info(__METHOD__ . '. PushToken: ' . $pushEvent->getPushToken() . '. LogMessage: ' . $logMessage);
                }

            }
            catch (\Exception $e) {
                $this->log()->error(__METHOD__ . '. PushToken: ' . $pushEvent->getPushToken() . '. Exception: ' . $e->getMessage());
                $pushEvent->setServiceResponseError($e->getMessage());
            }
            $pushEvent->setSuccessExec(ApiPushEvent::EXEC_SUCCESS_CODE);
            $this->apiPushEventRepository->update($pushEvent);
        }
    }

    public function execPushEventsForIos()
    {
        $pushEvents = $this->apiPushEventRepository->findForIos();

        $adapter = new \FourPaws\External\ApplePushNotificationAdapter([
            'certificate' => Application::getInstance()->getRootDir() . '/app/config/apple-push-notification-cert-new.pem',
            'passPhrase' => 'lapy'
        ]);
        $pushManager = new \Sly\NotificationPusher\PushManager(\Sly\NotificationPusher\PushManager::ENVIRONMENT_PROD);

        $pushId = [];

        if (count($pushEvents) > 0) {
            foreach ($pushEvents as $pushEvent) {
                try {
                    $message = new Message($pushEvent->getMessageText());

                    $message->setOption('badge', 1);
                    $message->setOption('sound', '');
                    $message->setOption('custom', [
                        'type' => $pushEvent->getMessageTypeEntity()->getXmlId(),
                        'id' => $pushEvent->getEventId()
                    ]);
                    $message->setOption('type', $pushEvent->getMessageTypeEntity()->getXmlId());
                    $message->setOption('id', $pushEvent->getEventId());


                    try {
                        $device = new Device($pushEvent->getPushToken());
                    } catch (AdapterException $adapterException) {
                        continue;
                    }
                    $device->setParameter('badge', 1);
                    $device->setParameter('sound', '');
                    $device->setParameter('type', $pushEvent->getMessageTypeEntity()->getXmlId());
                    $device->setParameter('id', $pushEvent->getEventId());
                    $device->setParameter('custom', [
                        'type' => $pushEvent->getMessageTypeEntity()->getXmlId(),
                        'id' => $pushEvent->getEventId()
                    ]);

                    $deviceArr = new DeviceCollection([
                        $device
                    ]);
                    $push = new Push($adapter, $deviceArr, $message);

                    $pushManager->add($push);

                    $pushId[$pushEvent->getPushToken()] = $pushEvent;
                } catch (\Exception $e) {
                    $pushEvent->setServiceResponseError($e->getMessage());
                }
            }

            try {
                $pushManager->push();
            } catch (\Exception $adapterException) {
                $this->log()->error('Ошибка при отправке push ios ' . $adapterException->getMessage());
            }

            $response = [];

            try {
                $response = $pushManager->getResponse()->getParsedResponses();
            } catch (\Exception $e) {
                $adapter->getOpenedClient()->close();
                $this->log()->error('Ошибка при отправке push ios ' . $e->getMessage());
            }

            $haveThrow = false;

            foreach ($response as $responseItem) {
                if ($responseItem['token'] != 0) {
                    $haveThrow = true;
                }
            }

            foreach ($response as $token => $responseItem) {
                if ($haveThrow) {
                    if ($responseItem['token'] != 0) {
                        $pushId[$token]->setServiceResponseStatus($responseItem['token']);
                        $pushId[$token]->setSuccessExec($responseItem['token'] > 0 ? ApiPushEvent::EXEC_FAIL_CODE : ApiPushEvent::EXEC_SUCCESS_CODE);
                        $this->apiPushEventRepository->update($pushId[$token]);
                    }
                } else {
                    $pushId[$token]->setServiceResponseStatus($responseItem['token']);
                    $pushId[$token]->setSuccessExec($responseItem['token'] > 0 ? ApiPushEvent::EXEC_FAIL_CODE : ApiPushEvent::EXEC_SUCCESS_CODE);
                    $this->apiPushEventRepository->update($pushId[$token]);
                }
            }
        }
    }


    /**
     * Ищет сессии пользователей для конкретного push-сообщения
     * @param ApiPushMessage $pushMessage
     * @return array|ApiUserSession[]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function findUsersSessions(ApiPushMessage $pushMessage)
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

        $userFilter = [];

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

        if (empty($userFilter) && !$pushMessage->getIsSendingToAllUsers()) {
            // если адресаты не указаны - ничего отправлять не нужно
            return [];
        }

        $userFilter['LOGIC'] = 'OR';

        if ($pushMessage->getIsSendingToAllUsers()) { // если стоит галка "Отправить всем пользователям", то игнорируются указанные группы и отдельные пользователи
            $userFilter = [
                'LOGIC' => 'OR',
            ];
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
     * @param ApiPushMessage $pushMessage
     * @param ApiUserSession $session
     * @return ApiPushEvent
     */
    protected function convertToPushEvent(ApiPushMessage $pushMessage, ApiUserSession $session)
    {
        return (new ApiPushEvent())
            ->setPlatform($session->getPlatform())
            ->setPushToken($session->getPushToken())
            ->setUserId($session->getUserId() ?: 0)
            ->setMessageId($pushMessage->getId())
            ->setDateTimeExec($pushMessage->getStartSend());
    }

    /**
     * обрабатывает файл с номерами телефонов, прикреплённый к записи push сообщения
     * @param ApiPushMessage $pushMessage
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Exception
     */
    protected function parseFile($pushMessage)
    {
        if (!$pushMessage->getFileId()) {
            return false;
        }

        $rows = file($_SERVER['DOCUMENT_ROOT'] . $pushMessage->getFilePath());

        $phones = [];
        foreach ($rows as $row) {
            $phone = $this->normalizePhoneNumber($row);
            $phones[$phone] = $phone;
        }
        $phones = $this->limitToAllowedPhoneNumbersAmount(array_values($phones));
        $userIds = $this->getUserIdsByPhoneNumbers($phones, $pushMessage->getTypeEntity()->getXmlId());

        $pushMessage->setUserIds($userIds);

        $data = $this->transformer->toArray(
            $pushMessage,
            SerializationContext::create()->setGroups([CrudGroups::UPDATE])
        );
        $hlBlockPushMessages = Application::getHlBlockDataManager('bx.hlblock.pushmessages');
        $hlBlockPushMessages->update($pushMessage->getId(), $data);
        return true;
    }

    /**
     * @param $phone
     * @return string
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        return substr(preg_replace('/\D/', '', $phone), -10);
    }

    /**
     * @param array $phones
     * @return array
     */
    protected function limitToAllowedPhoneNumbersAmount(array $phones): array
    {
        return array_slice($phones,0,static::MAX_PHONES_AMOUNT_PER_REQUEST);
    }

    /**
     * метод проверяет разрешено ли отправлять push'и данного типа на указанные номера телефонов
     *
     * принимает массив номеров телефонов
     * возвращает ID пользователей, прошедших проверку
     * если установлен параметр saveToLog - записывает в файл пользователей, не прошедших проверку
     * @param string[] $phoneNumbers
     * @param string $typeCode
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getUserIdsByPhoneNumbers(array $phoneNumbers, string $typeCode): array
    {
        $userIds = [];

        if (!(is_array($phoneNumbers) && !empty($phoneNumbers))) {
            return $userIds;
        }

        $users = $this->userRepository
            ->findBy([
                '=PERSONAL_PHONE' => $phoneNumbers,
                '!PERSONAL_PHONE' => null,
            ]);

        $foundPhoneNumbers = [];
        foreach ($users as $user) {
            $personalPhone = $user->getPersonalPhone();
            $userSession = $this->apiUserSessionRepository->findBy([
                '=USER_ID' => $user->getId(),
            ], ['ID' => 'DESC'], 1)[0];

            if (!$userSession) {
                $this->log()->warning("PushEventService: у пользователя с номером телефона $personalPhone нет сессий в мобильном приложении");
            } else if (!($userSession->getPlatform() && $userSession->getPushToken())) {
                $this->log()->warning("PushEventService: у пользователя с номером телефона $personalPhone не установлено мобильное приложение");
            } else {
                if ($this->shouldSendPushMessage($user, $typeCode)) {
                    $userIds[$user->getId()] = $user->getId();
                } else {
                    $this->log()->warning("PushEventService: пользователь с номером телефона $personalPhone отключил push уведомления");
                }
            }

            $foundPhoneNumbers[] = $personalPhone;
        }

        $notFoundPhoneNumbers = array_diff($phoneNumbers, $foundPhoneNumbers);
        if (!empty($notFoundPhoneNumbers)) {
            foreach ($notFoundPhoneNumbers as $phoneNumber) {
                $this->log()->warning("PushEventService: пользователь с номером телефона $phoneNumber не найден");
            }
        }

        return array_values($userIds);
    }

    /**
     * @param \FourPaws\UserBundle\Entity\User $user
     * @param $typeCode
     * @return bool
     */
    protected function shouldSendPushMessage(\FourPaws\UserBundle\Entity\User $user, string $typeCode): bool
    {
        return (
            ($typeCode == 'news' && $user->isSendNewsMsg())
            || ($typeCode == 'action' && $user->isSendInterviewMsg())
            || ($typeCode == 'status' && $user->isSendOrderStatusMsg())
            || ($typeCode == 'order_review' && $user->isSendFeedbackMsg())
            || ($typeCode == 'message')
        );
    }
}
