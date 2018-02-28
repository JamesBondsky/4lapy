<?php

namespace FourPaws\External;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Decorators\FullHrefDecorator;
use FourPaws\External\Exception\ExpertsenderServiceException;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Service\ConfirmCodeInterface;
use FourPaws\UserBundle\Service\ConfirmCodeService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LinguaLeo\ExpertSender\Entities\Property;
use LinguaLeo\ExpertSender\Entities\Receiver;
use LinguaLeo\ExpertSender\Entities\Snippet;
use LinguaLeo\ExpertSender\ExpertSender;
use LinguaLeo\ExpertSender\ExpertSenderException;
use LinguaLeo\ExpertSender\Request\AddUserToList;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ExpertsenderService
 *
 * @package FourPaws\External
 */
class ExpertsenderService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $client;
    private $guzzleClient;
    private $key;
    private $url;

    /**
     * ExpertsenderService constructor.
     *
     * @throws ApplicationCreateException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function __construct()
    {
        $client = new Client();
        $this->guzzleClient = $client;

        list($url, $key) = \array_values(Application::getInstance()->getContainer()->getParameter('expertsender'));
        $this->key = $key;
        $this->url = $url;
        $this->client = new ExpertSender($url, $key, $client);

        $this->setLogger(LoggerFactory::create('expertsender'));
    }

    /**
     * @param User $user
     *
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ExpertsenderServiceException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function sendEmailAfterRegister(User $user): bool
    {
        if (!empty($user->getEmail())) {
            $addUserToList = new AddUserToList();
            $addUserToList->setForce(true);
            $addUserToList->setMode('AddAndUpdate');
            $addUserToList->setTrackingCode('reg_form');
            $addUserToList->setListId(178);
            $addUserToList->setEmail($user->getEmail());
            $addUserToList->setFirstName($user->getName());
            $addUserToList->setLastName($user->getLastName());
            /** флаг подписки на новости */
            $addUserToList->addProperty(new Property(23, 'boolean', 0));
            /** флаг регистрации */
            $addUserToList->addProperty(new Property(47, 'boolean', true));
            try {
                /** хеш строка для подтверждения мыла */
                /** @var ConfirmCodeService $confirmService */
                $confirmService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                $generatedHash = $confirmService::getConfirmHash($user->getEmail());
                $confirmService::setGeneratedCode($generatedHash, 'email');
                $addUserToList->addProperty(new Property(10, 'string', $generatedHash));
                unset($generatedHash, $confirmService, $user);
                /** ip юзверя */
                $addUserToList->addProperty(new Property(48, 'string',
                    BitrixApplication::getInstance()->getContext()->getServer()->get('REMOTE_ADDR')));
                $apiResult = $this->client->addUserToList($addUserToList);
                if ($apiResult->isOk()) {
                    return true;
                }
            } catch (SystemException $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            } catch (GuzzleException $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            } catch (\Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return false;
    }

    /**
     * @param string $email
     *
     * @return bool
     * @throws ExpertsenderServiceException
     */
    public function sendChangePasswordByProfile(string $email): bool
    {
        if(!empty($email)) {
            try {
                $receiver = new Receiver($email);
                $apiResult = $this->client->sendTransactional(7073, $receiver);
                if ($apiResult->isOk()) {
                    return true;
                }
            } catch (ExpertSenderException $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            } catch (GuzzleException $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            }
        }

        return false;
    }

    /**
     * @param User   $user
     * @param string $backUrl
     *
     * @return bool
     * @throws ExpertsenderServiceException
     */
    public function sendForgotPassword(User $user, string $backUrl = ''): bool
    {
        if (!empty($user->getEmail())) {
            try {
                /** хеш строка для подтверждения мыла */
                /** @var ConfirmCodeService $confirmService */
                $confirmService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                $generatedHash = $confirmService::getConfirmHash($user->getEmail());
                $receiver = new Receiver($user->getEmail());
                $backUrlText = !empty($backUrl) ? '&backurl=' . $backUrl : '';
                $snippets = [
                    new Snippet('user_name', $user->getName(), true),
                    new Snippet('link', (new FullHrefDecorator('/forgot-password/?hash=' . $generatedHash . '&email=' . $user->getEmail() . $backUrlText))->getFullPublicPath(),true),
                ];
                $apiResult = $this->client->sendTransactional(7072, $receiver, $snippets);
                if ($apiResult->isOk()) {
                    return true;
                }
            } catch (ExpertSenderException $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            } catch (GuzzleException $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            } catch (ApplicationCreateException $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            } catch (\Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            }
        }
        return false;
    }

    /**
     * @param User $oldUser
     * @param User $curUser
     *
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws ExpertsenderServiceException
     */
    public function sendChangeEmail(User $oldUser, User $curUser): bool
    {
        if(!empty($oldUser->getEmail()) && !empty($curUser->getEmail())) {
            try {
                $expertSenderId = 0;
                $userIdResult = $this->client->getUserId($oldUser->getEmail());
                if ($userIdResult->isOk()) {
                    $expertSenderId = $userIdResult->getId();
                }

                $continue = false;
                if ($expertSenderId > 0) {
                    /** @todo должно быть письмо с верификацией мыла - под него подогнать проверку */
                    $addUserToList = new AddUserToList();
                    $addUserToList->setForce(true);
                    $addUserToList->setMode('AddAndUpdate');
                    $addUserToList->setListId(178);
                    $addUserToList->setEmail($curUser->getEmail());
                    $addUserToList->setId($expertSenderId);

                    $apiResult = $this->client->addUserToList($addUserToList);
                    if ($apiResult->isOk()) {
                        $continue = true;
                    }
                } else {
                    /** @todo должно быть письмо с верификацией мыла - под него подогнать проверку */
                    $addUserToList = new AddUserToList();
                    $addUserToList->setForce(true);
                    $addUserToList->setMode('AddAndUpdate');
                    $addUserToList->setTrackingCode('reg_form');
                    $addUserToList->setListId(178);
                    $addUserToList->setEmail($curUser->getEmail());
                    $addUserToList->setFirstName($curUser->getName());
                    $addUserToList->setLastName($curUser->getLastName());
                    /** флаг подписки на новости */
                    $addUserToList->addProperty(new Property(23, 'boolean', 0));
                    /** флаг регистрации */
                    $addUserToList->addProperty(new Property(47, 'boolean', 0));

                    /** хеш строка для подтверждения мыла */
                    /** @var ConfirmCodeService $confirmService */
                    $confirmService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                    $generatedHash = $confirmService::getConfirmHash($curUser->getEmail());
                    $confirmService::setGeneratedCode($generatedHash, 'email');
                    $addUserToList->addProperty(new Property(10, 'string', $generatedHash));
                    /** ip юзверя */
                    $addUserToList->addProperty(new Property(48, 'string',
                        BitrixApplication::getInstance()->getContext()->getServer()->get('REMOTE_ADDR')));
                    $apiResult = $this->client->addUserToList($addUserToList);
                    if ($apiResult->isOk()) {
                        $continue = true;
                    }
                }

                if ($continue) {
                    $receiver = new Receiver($curUser->getEmail());
                    $apiResult = $this->client->sendTransactional(7071, $receiver);
                    if ($apiResult->isOk()) {
                        return true;
                    }
                }
            } catch (GuzzleException $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            } catch (\Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            }
        }

        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ExpertsenderServiceException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function sendEmailSubscribeNews(User $user): bool
    {
        if (!empty($user->getEmail())) {
            try {
                $expertSenderId = 0;
                $userIdResult = $this->client->getUserId($user->getEmail());
                if ($userIdResult->isOk()) {
                    $expertSenderId = $userIdResult->getId();
                }

                if ($expertSenderId > 0) {
                    /** @todo должно быть письмо с верификацией мыла - под него подогнать проверку */
                    $addUserToList = new AddUserToList();
                    $addUserToList->setForce(true);
                    $addUserToList->setMode('AddAndUpdate');
                    $addUserToList->setTrackingCode('all_popup');
                    $addUserToList->setListId(178);
                    $addUserToList->setId($expertSenderId);
                    /** флаг подписки на новости */
                    $addUserToList->addProperty(new Property(23, 'boolean', true));

                    /** @todo првоерить надо или нет - будет ли подтверждеине подписки или нет*/
                    /** хеш строка для подтверждения мыла */
                    /** @var ConfirmCodeService $confirmService */
//                    $confirmService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
//                    $generatedHash = $confirmService::getConfirmHash($user->getEmail());
//                    $confirmService::setGeneratedCode($generatedHash, 'email');
//                    $addUserToList->addProperty(new Property(10, 'string', $generatedHash));

                    $apiResult = $this->client->addUserToList($addUserToList);
                    if ($apiResult->isOk()) {
                        return true;
                    }

                } else {
                    /** @todo должно быть письмо с верификацией мыла - под него подогнать проверку */
                    $addUserToList = new AddUserToList();
                    $addUserToList->setForce(true);
                    $addUserToList->setMode('AddAndUpdate');
                    $addUserToList->setTrackingCode('all_popup');
                    $addUserToList->setListId(178);
                    $addUserToList->setEmail($user->getEmail());
                    $addUserToList->setFirstName($user->getName());
                    $addUserToList->setLastName($user->getLastName());
                    /** флаг подписки на новости */
                    $addUserToList->addProperty(new Property(23, 'boolean', true));
                    /** флаг регистрации */
                    $addUserToList->addProperty(new Property(47, 'boolean', 0));

                    /** хеш строка для подтверждения мыла */
                    /** @var ConfirmCodeService $confirmService */
                    $confirmService = Application::getInstance()->getContainer()->get(ConfirmCodeInterface::class);
                    $generatedHash = $confirmService::getConfirmHash($user->getEmail());
                    $confirmService::setGeneratedCode($generatedHash, 'email');
                    $addUserToList->addProperty(new Property(10, 'string', $generatedHash));
                    /** ip юзверя */
                    $addUserToList->addProperty(new Property(48, 'string',
                        BitrixApplication::getInstance()->getContext()->getServer()->get('REMOTE_ADDR')));
                    $apiResult = $this->client->addUserToList($addUserToList);
                    if ($apiResult->isOk()) {
                        return true;
                    }
                }
            } catch (SystemException $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            } catch (GuzzleException $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            } catch (\Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            }
        }
        return false;
    }

    /**
     * @param User $user
     *
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ExpertsenderServiceException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function sendEmailUnSubscribeNews(User $user): bool
    {
        if (!empty($user->getEmail())) {
            try {
                $expertSenderId = 0;
                $userIdResult = $this->client->getUserId($user->getEmail());
                if ($userIdResult->isOk()) {
                    $expertSenderId = $userIdResult->getId();
                }

                if ($expertSenderId > 0) {
                    $addUserToList = new AddUserToList();
                    $addUserToList->setForce(true);
                    $addUserToList->setMode('AddAndUpdate');
                    $addUserToList->setTrackingCode('all_popup');
                    $addUserToList->setListId(178);
                    $addUserToList->setId($expertSenderId);
                    /** флаг подписки на новости */
                    $addUserToList->addProperty(new Property(23, 'boolean', 0));

                    $apiResult = $this->client->addUserToList($addUserToList);
                    if ($apiResult->isOk()) {
                        return true;
                    }

                }
                else{
                    return true;
                }
            } catch (SystemException $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            } catch (GuzzleException $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            } catch (\Exception $e) {
                throw new ExpertsenderServiceException($e->getMessage(), $e->getCode());
            }
        }
        return false;
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public function checkConfirmEmail(string $email): bool
    {
        //Проверяем статус активного или неподписанного в списке
        try {
            $response = $this->guzzleClient->get($this->url . '/Api/Subscribers?apiKey=' . $this->key . '&email=' . $email . '&option=Short');
            $activeLists = [];
            if ($response->getStatusCode() === 200) {
                $xml = new \SimpleXMLElement($response->getBody()->getContents());
                if (!(bool)$xml->Data->BlackList) {
                    foreach ((array)$xml->Data->StateOnLists as $StateOnList) {
                        if ((string)$StateOnList->Status === 'Active' || (string)$StateOnList->Status === 'Unsubscribed') {
                            $activeLists[] = (int)$StateOnList->ListId;
                        }
                    }
                }
                unset($xml);
            }

            if (\in_array(178, $activeLists, true)) {
                return true;
            }
        } catch (GuzzleException $e) {
            $this->logger->critical('Переписать нахер. Так делатть НЕЛЬЗЯ.');
        }

        return false;
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public function checkConfirmEmailSubscribe(string $email): bool
    {
        $response = $this->guzzleClient->get($this->url.'/Api/Subscribers?apiKey='.$this->key.'&email='.$email.'&option=Short');
        $activeLists = [];
        if($response->getStatusCode() === 200){
            $xml = new \SimpleXMLElement($response->getBody()->getContents());
            if(!(bool)$xml->Data->BlackList) {
                foreach ((array)$xml->Data->StateOnLists as $StateOnList) {
                    if ((string)$StateOnList->Status === 'Active') {
                        $activeLists[] = (int)$StateOnList->ListId;
                    }
                }
            }
            unset($xml);
        }

        if(\in_array(178, $activeLists, true)){
            return true;
        }
        return false;
    }
}
