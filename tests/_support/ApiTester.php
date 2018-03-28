<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

/**
 * Class ApiTester
 * @todo more by Db manipulation
 */
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    /**
     * @throws Exception
     */
    public function createToken()
    {
        $fuserId = $this->haveInDatabase('b_sale_fuser', [
            'CODE' => md5(uniqid('fuser', true)),
        ]);

        $this->assertGreaterThan(0, $fuserId);

        $token = md5(uniqid('token', true));
        $tokenId = $this->haveInDatabase('api_user_session', [
            'USER_AGENT'  => 'Symfony BrowserKit',
            'REMOTE_ADDR' => '127.0.0.1',
            'FUSER_ID'    => $fuserId,
            'TOKEN'       => $token,
        ]);

        $this->assertGreaterThan(0, $tokenId);
        return $token;
    }

    public function createDummyUser()
    {
        $login = md5(uniqid('email', true)) . '@4lapy.ru';

        $data = [
            'ACTIVE'         => 'Y',
            'EMAIL'          => $login,
            'LOGIN'          => $login,
            'PASSWORD'       => md5(uniqid('pass', true)),
            'NAME'           => md5(uniqid('name', true)),
            'LAST_NAME'      => md5(uniqid('lastname', true)),
            'PERSONAL_PHONE' => '916' . random_int(1000000, 9999999),
        ];

        $salt = substr(md5(uniqid('salt', true)), 0, 8);
        $saltedPassword = md5($salt . $data['PASSWORD']);
        $addData = $data;
        $addData['PASSWORD'] = $salt . $saltedPassword;
        $userId = $this->haveInDatabase('b_user', $addData);

        $this->haveInDatabase('b_uts_user', [
            'VALUE_ID'           => $userId,
            'UF_PHONE_CONFIRMED' => 1,
        ]);

        $data['ID'] = $userId;

        $this->haveInDatabase('b_user_group', [
            'USER_ID'  => $userId,
            'GROUP_ID' => 6,
        ]);

        $fuserId = $this->haveInDatabase('b_sale_fuser', [
            'USER_ID' => $userId,
        ]);
        $data['FUSER_ID'] = $fuserId;

        return $data;
    }

    public function login(int $userId, string $token): void
    {
        if ($fuserId = $this->grabFromDatabase('b_sale_fuser', 'ID', ['USER_ID' => $userId,])) {
            $this->updateInDatabase(
                'api_user_session',
                [
                    'FUSER_ID' => $fuserId,
                ],
                [
                    'TOKEN' => $token,
                ]
            );
        } else {
            $fuserId = $this->grabFromDatabase('b_sale_fuser', 'FUSER_ID', [
                'TOKEN' => $token,
            ]);
            $this->assertGreaterThan(0, $fuserId);
            $this->updateInDatabase(
                'b_sale_fuser',
                [
                    'USER_ID' => $userId,
                ],
                [
                    'FUSER_ID' => $fuserId,
                ]
            );
        }
    }

    /**
     * @param int $userId
     * @throws Exception
     * @throws \Codeception\Exception\ModuleException
     * @return string
     */
    public function getCard(int $userId): string
    {
        $cardNumber = '267700' . random_int(1000000, 9999999);
        $data = $this->grabColumnsFromDatabase('b_uts_user', [
            'VALUE_ID' => $userId,
        ]);

        if ($data['UF_DISCOUNT_CARD']) {
            return $data['UF_DISCOUNT_CARD'];
        }

        if (!$data['UF_DISCOUNT_CARD'] ?? '') {
            $this->updateInDatabase('b_uts_user', [
                'UF_DISCOUNT_CARD' => $cardNumber,
            ]);
        }

        return $cardNumber;
    }

    /**
     * @param int $userId
     * @throws \Codeception\Exception\ModuleException
     * @throws Exception
     * @return array
     */
    public function getSettings(int $userId): array
    {
        $data = $this->grabColumnsFromDatabase('b_uts_user', [
            'VALUE_ID' => $userId,
        ]);

        return [
            'interview_messaging_enabled' => $data['UF_INTERVIEW_MES'] === '1',
            'bonus_messaging_enabled'     => $data['UF_BONUS_MES'] === '1',
            'feedback_messaging_enabled'  => $data['UF_FEEDBACK_MES'] === '1',
            'push_order_status'           => $data['UF_PUSH_ORD_STAT'] === '1',
            'push_news'                   => $data['UF_PUSH_NEWS'] === '1',
            'push_account_change'         => $data['UF_PUSH_ACC_CHANGE'] === '1',
            'sms_messaging_enabled'       => $data['UF_SMS_MES'] === '1',
            'email_messaging_enabled'     => $data['UF_EMAIL_MES'] === '1',
            'gps_messaging_enabled'       => $data['UF_GPS_MESS'] === '1',
        ];
    }

    /**
     * @param int $userId
     * @throws Exception
     * @throws \Codeception\Exception\ModuleException
     * @return array
     */
    public function createAddress(int $userId): array
    {
        $city = $this->grabColumnsFromDatabase('b_sale_location', [
            'TYPE_ID' => 5,
        ]);

        $fields = [
            'UF_USER_ID'       => $userId,
            'UF_NAME'          => md5(random_bytes(1024)),
            'UF_CITY_LOCATION' => $city['CODE'],
            'UF_STREET'        => md5(random_bytes(1024)),
            'UF_HOUSE'         => md5(random_bytes(1024)),
            'UF_HOUSING'       => md5(random_bytes(1024)),
            'UF_ENTRANCE'      => md5(random_bytes(1024)),
            'UF_FLOOR'         => md5(random_bytes(1024)),
            'UF_FLAT'          => md5(random_bytes(1024)),
            'UF_INTERCOM_CODE' => md5(random_bytes(1024)),
            'UF_MAIN'          => random_int(0, 1),
            'UF_DETAILS'       => md5(random_bytes(1024)),
        ];

        $id = $this->haveInDatabase('adv_adress', $fields);
        $fields['ID'] = $id;
        return $fields;
    }

    /**
     * @param int $errorCode
     * @throws Exception
     */
    public function assertContainsError(int $errorCode): void
    {
        $data = $this->grabDataFromResponseByJsonPath('$.error[0]');
        $error = reset($data);

        $code = $error['code'] ?? 0;
        $this->assertEquals($errorCode, (int)$code);
    }
}
