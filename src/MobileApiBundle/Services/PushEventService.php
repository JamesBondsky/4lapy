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
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Repository\UserRepository;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sly\NotificationPusher\Adapter\Apns;
use Sly\NotificationPusher\Collection\DeviceCollection;
use Sly\NotificationPusher\Exception\AdapterException;
use Sly\NotificationPusher\Model\Device;
use Sly\NotificationPusher\Model\Message;
use Sly\NotificationPusher\Model\Push;
use tests\units\Sly\NotificationPusher\PushManager;
use FourPaws\BitrixOrm\Table\EnumUserFieldTable;

class PushEventService
{

    use LazyLoggerAwareTrait;

    const MAX_PHONES_AMOUNT_PER_REQUEST = 100;

    /**
     * @var ArrayTransformerInterface
     */
    public $transformer;

    /**
     * @var ApiUserSessionRepository
     */
    private $apiUserSessionRepository;

    /**
     * @var ApiPushEventRepository
     */
    public $apiPushEventRepository;

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

    /**
     * @var SerializerInterface $serializer
     */
    private $serializer;


    public function __construct(
        ArrayTransformerInterface $transformer,
        ApiUserSessionRepository $apiUserSessionRepository,
        ApiPushEventRepository $apiPushEventRepository,
        FireBaseCloudMessagingService $fireBaseCloudMessagingService,
        ApplePushNotificationService $applePushNotificationService,
        UserRepository $userRepository,
        SerializerInterface $serializer
    )
    {
        $this->transformer = $transformer;
        $this->apiUserSessionRepository = $apiUserSessionRepository;
        $this->apiPushEventRepository = $apiPushEventRepository;
        $this->fireBaseCloudMessagingService = $fireBaseCloudMessagingService;
        $this->applePushNotificationService = $applePushNotificationService;
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
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
                '>=UF_START_SEND' => (new \Bitrix\Main\Type\DateTime())->add('-10 minutes')->format('d.m.Y H:i:00'),
                '<=UF_START_SEND' => (new \Bitrix\Main\Type\DateTime())->add('-10 minutes')->format('d.m.Y H:i:59'),
            ])
            ->setSelect([
                '*',
            ])
            ->setLimit(500)
            ->exec();

        $dataFetch = $res->fetchAll();

        $dataFetch = $this->modifyDataFetch($dataFetch);

        $pushMessages = $this->transformer->fromArray(
            $dataFetch,
            'array<' . ApiPushMessage::class . '>'
        );

        $hlBlockPushMessages = Application::getHlBlockDataManager('bx.hlblock.pushmessages');

        foreach ($dataFetch as &$pushItem) {
            $pushItem['UF_START_SEND'] = (string)$pushItem['UF_START_SEND'];
        }

        $producer = Application::getInstance()->getContainer()->get('old_sound_rabbit_mq.push_file_processing_producer');

        /** @var ApiPushMessage $pushMessage */
        foreach ($pushMessages as $pushMessage) {
//            $this->parseFile($pushMessage);
//            $pushMessage->setFileId(0);
//
//            $data = $this->transformer->toArray(
//                $pushMessage,
//                SerializationContext::create()->setGroups([CrudGroups::UPDATE])
//            );

            $currentItem = [];

            foreach ($dataFetch as $dataFetchItem) {
                if ($dataFetchItem['ID'] == $pushMessage->getId()) {
                    $currentItem = $dataFetchItem;
                    break;
                }
            }

            if (!empty($currentItem)) {
                $phones = $this->parseFile($pushMessage->getFilePath());
                foreach ($phones as $phoneItem) {
                    if ($phoneItem) {
                        $producer->publish(json_encode([
                            'pushMessage' => $currentItem,
                            'phone' => $phoneItem
                        ]));
                    }
                }
            }

            // деактивируем push-сообщение
            $hlBlockPushMessages->update($pushMessage->getId(), [
                'UF_ACTIVE' => false,
            ]);
        }

//        if (count($dataFetch) > 0) {
//            $producer = Application::getInstance()->getContainer()->get('old_sound_rabbit_mq.push_file_processing_producer');
//            $producer->publish(json_encode($dataFetch));
//        }
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
                '<=UF_START_SEND' => (new \Bitrix\Main\Type\DateTime())->add('+10 minutes')->format('d.m.Y H:i:s'),
                'UF_FILE' => false,
            ])
            ->setSelect([
                '*'
            ])
            ->setLimit(500)
            ->exec();

        $dataFetch = $res->fetchAll();

        $dataFetch = $this->modifyDataFetch($dataFetch);

        /** @var ApiPushMessage[] $pushMessages */
        $pushMessages = $this->transformer->fromArray(
            $dataFetch,
            'array<' . ApiPushMessage::class . '>'
        );

        foreach ($dataFetch as &$pushItem) {
            $pushItem['UF_START_SEND'] = (string)$pushItem['UF_START_SEND'];
        }

        if (count($dataFetch) > 0) {
            /** @noinspection MissingService */
            $producer = Application::getInstance()->getContainer()->get('old_sound_rabbit_mq.push_processing_producer');
            $producer->publish(json_encode($dataFetch));
        }

        foreach ($pushMessages as $pushMessage) {
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
                $eventId = $this->getEventId($pushEvent);

                $response = $this->fireBaseCloudMessagingService->sendNotification(
                    $pushEvent->getPushToken(),
                    $pushEvent->getMessageText(),
                    $eventId,
                    $pushEvent->getMessageTypeEntity()->getXmlId(),
                    $pushEvent->getMessageTitle(),
                    $pushEvent->getPhotoUrl()
                );

                $execCode = $response->getStatusCode() === 200 ? ApiPushEvent::EXEC_SUCCESS_CODE : ApiPushEvent::EXEC_FAIL_CODE;
                $pushEvent->setSuccessExec($execCode);
                $pushEvent->setServiceResponseStatus($response->getStatusCode());
            } catch (\Exception $e) {
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
                $eventId = $this->getEventId($pushEvent);

                $this->applePushNotificationService->sendNotification(
                    $pushEvent->getPushToken(),
                    $pushEvent->getMessageText(),
                    $eventId,
                    $pushEvent->getMessageTypeEntity()->getXmlId()
                );

                foreach ($this->applePushNotificationService->getLogMessages() as $logMessage) {
                    $this->log()->info(__METHOD__ . '. PushToken: ' . $pushEvent->getPushToken() . '. LogMessage: ' . $logMessage);
                }

            } catch (\Exception $e) {
                $this->log()->error(__METHOD__ . '. PushToken: ' . $pushEvent->getPushToken() . '. Exception: ' . $e->getMessage());
                $pushEvent->setServiceResponseError($e->getMessage());
            }
            $pushEvent->setSuccessExec(ApiPushEvent::EXEC_SUCCESS_CODE);
            $this->apiPushEventRepository->update($pushEvent);
        }
    }

    public function execPushEventsForIos($pushEvents = null)
    {
//        $pushEvents = $this->apiPushEventRepository->findForIos();

        $adapter = new \FourPaws\External\ApplePushNotificationAdapter([
            'certificate' => Application::getInstance()->getRootDir() . '/app/config/apple-push-notification-cert-new.pem',
            'passPhrase' => 'lapy'
        ]);
        $pushManager = new \Sly\NotificationPusher\PushManager(\Sly\NotificationPusher\PushManager::ENVIRONMENT_PROD);

        $pushId = [];

        if (count($pushEvents) > 0) {
            foreach ($pushEvents as $pushEvent) {
                try {
                    $eventId = $this->getEventId($pushEvent);

                    $categoryTitle = '';

                    $data = [
                        'aps'      => [
                            'mutable-content' => 1,
                            'alert'           => [
                               'title' => $pushEvent->getMessageTitle(),
                               'body'  => $pushEvent->getMessageText(),
                            ],
                            'sound'           => 'default',
                            'badge'           => 2,
                        ],
                        'photourl' => $pushEvent->getPhotoUrl(),
                        'type'     => $pushEvent->getMessageTypeEntity()->getXmlId(),
                        'id'       => $eventId,
                    ];

                    if ($data['photourl']) {
                        $data['aps']['category'] = 'PHOTO';
                        $data['photourl'] = getenv('SITE_URL') . $data['photourl'];
                    }

                    if ($data['type'] == 'category') {
                        $categoryTitle = \Bitrix\Iblock\SectionTable::getList([
                            'select' => ['NAME'],
                            'filter' => ['=ID' => $data['id']]
                        ])->fetch()['NAME'];
                    }

                    $message = new Message($pushEvent->getMessageText());

                    $message->setOption('badge', 1);
                    $message->setOption('sound', 'default');
                    $message->setOption('mutable-content', 1);
                    $message->setOption('title', $pushEvent->getMessageTitle() ?? '');

                    $customArr = [
                        'type' => $pushEvent->getMessageTypeEntity()->getXmlId(),
                        'id' => $pushEvent->getEventId(),
                        'title' => $categoryTitle
                    ];

                    if ($data['photourl']) {
                        $customArr['photourl'] = $data['photourl'];

                        $message->setOption('category', 'PHOTO');
                    }

                    $message->setOption('custom', $customArr);


                    try {
                        $device = new Device($pushEvent->getPushToken());
                    } catch (AdapterException $adapterException) {
                        $pushEvent->setSuccessExec(ApiPushEvent::EXEC_FAIL_CODE);
                        $pushEvent->setServiceResponseStatus(22);
                        $this->apiPushEventRepository->update($pushEvent);
                        continue;
                    }

                    $deviceArr = new DeviceCollection([
                        $device,
                    ]);

                    try {
                        $push = new Push($adapter, $deviceArr, $message);
                    } catch (AdapterException $ae) {
                        //not support token
                        $pushEvent->setSuccessExec(ApiPushEvent::EXEC_FAIL_CODE);
                        $pushEvent->setServiceResponseStatus(33);
                        $this->apiPushEventRepository->update($pushEvent);
                        continue;
                    }

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
                $response = $pushManager->getResponse();
                if ($response) {
                    $response = $pushManager->getResponse()->getParsedResponses();
                } else {
                    $response = [];
                }
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
    public function findUsersSessions(ApiPushMessage $pushMessage)
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
    public function convertToPushEvent(ApiPushMessage $pushMessage, ApiUserSession $session)
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
     */
    /**
     * Получение номеров телфонов из файла
     * @param $filePath
     * @return array|bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public function parseFile($filePath)
    {
        if (!$filePath) {
            return false;
        }

//        $rows = file($_SERVER['DOCUMENT_ROOT'] . $pushMessage->getFilePath());
        $rows = file($_SERVER['DOCUMENT_ROOT'] . $filePath);

        $phones = [];
        foreach ($rows as $row) {
            $phone = $this->normalizePhoneNumber($row);
            $phones[$phone] = $phone;
        }

        if (count($phones) > 0) {
            $phones = array_unique($phones);
            $phones = array_values($phones);
        }

        return $phones;
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
    public function getUserIdsByPhoneNumbers(array $phoneNumbers, string $typeCode): array
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
        /** @var User $user */
        foreach ($users as $user) {
            if ($this->canSendPushMessage($user, $typeCode, true) && $this->shouldSendPushMessage($user, $typeCode)) {
                $foundPhoneNumbers[] = $user->getPersonalPhone();
                $userIds[]           = $user->getId();
            }
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
     * Проверяет возможность отправки пуша
     *
     * @param User   $user
     * @param string $typeCode
     * @param bool   $log
     * @return bool
     */
    public function canSendPushMessage(User $user, $typeCode = "", $log = false): bool
    {
        $result = true;

        $personalPhone = $user->getPersonalPhone();
        $userSession   = $this->apiUserSessionRepository->findBy([
            '=USER_ID' => $user->getId(),
        ], ['ID' => 'DESC'], 1)[0];

        if (!$userSession) {
            $result = false;
            if ($log) {
                $this->log()->warning("PushEventService: у пользователя с номером телефона $personalPhone нет сессий в мобильном приложении");
            }
        } elseif (!($userSession->getPlatform() && $userSession->getPushToken())) {
            $result = false;
            if ($log) {
                $this->log()->warning("PushEventService: у пользователя с номером телефона $personalPhone не установлено мобильное приложение");
            }
        }

        return $result;
    }

    /**
     * @param \FourPaws\UserBundle\Entity\User $user
     * @param                                  $typeCode
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
            || ($typeCode == 'category')
        );
    }

    protected function getTypeCodes()
    {
        try {
            $types = EnumUserFieldTable::query()
                ->setSelect(['ID', 'XML_ID'])
                ->setFilter(['=USER_FIELD_ID' => 643])
                ->setCacheTtl('36000')
                ->exec();

            while ($type = $types->fetch()) {
                $result[$type['ID']] = $type['XML_ID'];
            }

            return $result;
        } catch (\Exception $e) {

        }
    }

    protected function modifyDataFetch($dataFetch)
    {
        try {
            $typeCodes = $this->getTypeCodes();

            foreach ($dataFetch as &$prePushItem) {
                if ($prePushItem['UF_USERS']) {
                    $usersNeededToBeDelete = [];

                    $users = $this->userRepository
                        ->findBy([
                            '=ID' => $prePushItem['UF_USERS'],
                        ]);

                    $typeCode = $typeCodes[$prePushItem['UF_TYPE']];

                    foreach ($users as $user) {
                        if (!$this->shouldSendPushMessage($user, $typeCode)) {
                            $usersNeededToBeDelete[] = $user->getId();
                        }
                    }

                    foreach ($prePushItem['UF_USERS'] as $pushUserKey => $pushUser) {
                        if (in_array($pushUser, $usersNeededToBeDelete)) {
                            unset($prePushItem['UF_USERS'][$pushUserKey]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {

        }

        return $dataFetch;
    }

    protected function getEventId(ApiPushEvent $pushEvent)
    {
        $eventId = $pushEvent->getEventId();

        if (!$eventId) {
            $eventId = $pushEvent->getOtherEventId();
        }

        return $eventId;
    }
}
