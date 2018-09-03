<?php

namespace FourPaws\FrontOffice\Traits;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use FourPaws\App\Application;
use FourPaws\External\SmsService;
use FourPaws\UserBundle\Entity\User;
use Psr\Log\LoggerInterface;

trait SmsTrait
{
    /**
     * @param User $user
     * @param string $smsText
     * @return Result
     */
    protected function sendUserRegistrationSms(User $user, string $smsText)
    {
        $phone = trim($user->getNormalizePersonalPhone());
        $password = $user->getPassword();
        $login = $user->getLogin();
        $text = str_replace(
            ['#LOGIN#', '#PASSWORD#', '#PHONE#'],
            [$login, $password, $phone],
            $smsText
        );

        return $this->sendSms($phone, $text);
    }

    /**
     * @param string $phone
     * @param string $text
     * @return Result
     */
    protected function sendSms($phone, $text)
    {
        $result = new Result();

        if ($phone === '') {
            $result->addError(
                new Error('Не задан телефон для отправки SMS', 'sendSmsEmptyPhone')
            );
        }
        if ($text === '') {
            $result->addError(
                new Error('Не задано сообщение SMS', 'sendSmsEmptyText')
            );
        }

        /** @var LoggerInterface $log */
        $log = method_exists($this, 'log') ? $this->log() : null;
        if ($log) {
            $log->info(
                __FUNCTION__,
                [
                    'phone' => $phone,
                    'resultSuccess' => $result->isSuccess()
                ]
            );
        }

        if ($result->isSuccess()) {
            try {
                /** @var SmsService $smsService */
                $smsService = Application::getInstance()->getContainer()->get('sms.service');
                $smsService->sendSmsImmediate($text, $phone);
            } catch (\Exception $exception) {
                $result->addError(
                    new Error($exception->getMessage(), 'sendSmsException')
                );
                if ($log) {
                    $log->error(
                        sprintf(
                            '%s exception: %s',
                            __FUNCTION__,
                            $exception->getMessage()
                        )
                    );
                }
            }
        }

        $result->setData(
            [
                'phone' => $phone,
                'text' => $text,
            ]
        );

        return $result;
    }
}
