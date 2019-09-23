<?php


namespace FourPaws\External\Manzana\Consumer;


use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application as App;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\MobileApiBundle\Tables\UserApiLastUsingTable;
use FourPaws\UserBundle\EventController\Event;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Exception\WrongContactMessageException;

class ManzanaContactMobileUpdateConsumer extends ManzanaConsumerBase
{

    protected $logName = 'ManzanaContactMobileUpdateConsumer';

    public function execute(AMQPMessage $message)
    {
        Event::disableEvents();
        $userData = $this->serializer->deserialize($message->getBody(), 'array', 'json');

        $userId = $userData['userId'];
        $personalPhone = $userData['personalPhone'];

        if (!$personalPhone || !$userId) {
            return true;
        }

        try {
            $personalPhone = PhoneHelper::getManzanaPhone($personalPhone);
        } catch (\Exception $e) {
            return true;
        }

        $currentDate = new DateTime();
        $fields = [
            'USER_ID' => $userId
        ];
        $getLastUsing = UserApiLastUsingTable::query()->setSelect(['ID', 'DATE_INSERT'])->addFilter('=USER_ID', $fields['USER_ID'])->setOrder(['ID' => 'DESC'])->exec()->fetch();
        if (!$getLastUsing || (isset($getLastUsing['DATE_INSERT']) && $getLastUsing['DATE_INSERT']->format('d.m.Y') != $currentDate->format('d.m.Y'))) {
            if ($getLastUsing) {
                $fields['DATE_INSERT'] = clone $currentDate;
                UserApiLastUsingTable::update($getLastUsing['ID'], $fields);
            } else {
                UserApiLastUsingTable::add($fields);
            }

            $client = new Client();
            $client->phone = PhoneHelper::getManzanaPhone($personalPhone);
            $client->haveMobileApp = true;
            $client->lastDateUseMobileApp = $currentDate->format(\DateTime::ATOM);

            if (strlen($client->firstName) > 50) {
                $this->log()->error(sprintf(
                    'Длина firstName больше 50 символов, разрешенных в Manzana. userId: %s. firstName: %s',
                    $userId,
                    $client->firstName
                ));
            } elseif ($client->phone) {
                $container = App::getInstance()->getContainer();
                /** @var ManzanaService $manzanaService */
                $manzanaService = $container->get('manzana.service');

                try {
                    $manzanaClient = $manzanaService->getContactByPhone($personalPhone);

                    $client->contactId = $manzanaClient->contactId;
                } catch (\Exception $e) {}

                try {
                    $manzanaService->updateContact($client);
                }  catch (ContactUpdateException | WrongContactMessageException $e) {
                    $this->log()->error(sprintf(
                        'Contact update error: %s',
                        $e->getMessage()
                    ));
                } catch (ManzanaServiceContactSearchMoreOneException $e) {
                    $this->log()->info(sprintf(
                        'Too many user`s found: %s',
                        $e->getMessage()
                    ));
                    /** не перезапускаем очередь */
                } catch (ManzanaServiceException $e) {
                    $this->log()->error(sprintf(
                        'Manzana contact mobile update consumer error: %s, message: %s',
                        $e->getMessage(),
                        $message->getBody()
                    ));

                    sleep(5);

                    //FIXME Не очень корректное решение - приводит к зацикливанию выполнения метода в случае перманентных ошибок в данном сообщении
                    // (текущее сообщение бесконечно помечается как обработанное и заново добавляется в очередь)
                    try {
                        $this->manzanaService->updateContactMobileAsync($userData);
                    } catch (ApplicationCreateException | ServiceNotFoundException | ServiceCircularReferenceException $e) {
                        $this->log()->error(sprintf(
                            'Manzana contact mobile update consumer /service/ error: %s, message: %s',
                            $e->getMessage(),
                            $message->getBody()
                        ));
                    }
                }
            }
        }

        Event::enableEvents();

        return true;
    }
}
