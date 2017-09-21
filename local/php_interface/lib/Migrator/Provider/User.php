<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Main\UserTable;

class User extends ProviderAbstract
{
    /**
     * $map - однозначное отображение ['поле на сервере' => 'поле на клиенте']
     *
     * @return array
     */
    public function getMap() : array
    {
        $map = array_diff(array_keys(array_filter(UserTable::getMap(), self::getScalarEntityMapFilter())),
                          [
                              $this->entity->getPrimary(),
                              'LID',
                          ]);
        
        $map = array_combine($map, $map);
        
        $map = array_merge($map,
                           [
                               'UF_DISC'              => 'UF_DISCOUNT_CARD',
                               'UF_ADDR'              => 'UF_ADDRESS',
                               'UF_CORRECTION_BASKET' => 'UF_CORRECTION_BASKET',
                               'UF_SHOP'              => 'UF_SHOP',
                               'UF_IS_ACTUAL_EMAIL'   => 'UF_EMAIL_CONFIRMED',
                               'UF_IS_ACTUAL_PHONE'   => 'UF_PHONE_CONFIRMED',
                               'UF_ADDR_TOWN'         => 'UF_ADDR_TOWN',
                               'UF_ADDR_STREET'       => 'UF_ADDR_STREET',
                               'UF_ADDR_HOME'         => 'UF_ADDR_HOME',
                               'UF_ADDR_CORP'         => 'UF_ADDR_CORP',
                               'UF_ADDR_KVART'        => 'UF_ADDR_ROOM',
                               'UF_ADDR_POD'          => 'UF_ADDR_POD',
                               'UF_ADDR_ETAG'         => 'UF_ADDR_FLOOR',
                               'UF_INTERVIEW_MES'     => 'UF_INTERVIEW_MES',
                               'UF_BONUS_MES'         => 'UF_BONUS_MES',
                               'UF_SMS_MES'           => 'UF_SMS_MES',
                               'UF_EMAIL_MES'         => 'UF_EMAIL_MES',
                               'UF_GPS_MESS'          => 'UF_GPS_MESS',
                               'UF_PUSH_ACC_CHANGE'   => 'UF_PUSH_ACC_CHANGE',
                               'UF_FEEDBACK_MES'      => 'UF_FEEDBACK_MES',
                               'UF_PUSH_ORD_STAT'     => 'UF_PUSH_ORD_STAT',
                               'UF_PUSH_NEWS'         => 'UF_PUSH_NEWS',
                               'GROUPS'               => 'GROUPS',
                               'CHECKWORD'            => 'CHECKWORD',
                           ]);
        
        return $map;
    }
    
    /**
     * @param array $data
     *
     * @return array
     */
    public function prepareData(array $data)
    {
        $data['PASSWORD'] .= '.';
        
        if ($data['EMAIL'] == $data['LOGIN']) {
            $data['LOGIN'] = $this->normalizeEmail($data['LOGIN']);
        }
        
        $data['EMAIL'] = $this->normalizeEmail($data['EMAIL']);
        
        $data['PERSONAL_PHONE'] = $this->normalizePhone((string)$data['PERSONAL_PHONE']);
        
        if ($this->isLoginPhone((string)$data['LOGIN'])) {
            $data['LOGIN'] = $this->normalizePhone($data['LOGIN']);
        }
        
        return parent::prepareData($data);
    }
    
    /**
     * @param string $phone
     *
     * @return bool
     */
    public function isLoginPhone(string $phone)
    {
        return (strlen(preg_replace('~\D~', '', $phone)) == strlen($phone)) && strlen($phone) >= 10;
    }
    
    /**
     * @param string $phone
     *
     * @return bool|mixed|string
     */
    public function normalizePhone(string $phone)
    {
        return NormalizePhone($phone);
    }
    
    /**
     * @param string $email
     *
     * @return string
     */
    public function normalizeEmail(string $email)
    {
        $email    = explode('@', $email);
        $email[0] = preg_replace('~\.~', '', $email[0]);
        
        return implode('@', $email);
    }
}