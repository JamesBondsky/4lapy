<?php


namespace FourPaws\SocServ;


use Bitrix\Socialservices\UserTable;

trait SocServiceHelper
{
    /**
     * проверка на существование пользователя
     *
     * @param array $socservUserFields
     * @return array|false
     * @throws \Bitrix\Main\ArgumentException
     */
    public function checkUser(array $socservUserFields)
    {
        $dbSocUser = UserTable::getList(array(
            'filter' => array(
                '=XML_ID'=>$socservUserFields['XML_ID'],
                '=EXTERNAL_AUTH_ID'=>$socservUserFields['EXTERNAL_AUTH_ID']
            ),
            'select' => array("ID", "USER_ID", "ACTIVE" => "USER.ACTIVE"),
        ));
        $socservUser = $dbSocUser->fetch();

        return $socservUser;
    }
}
