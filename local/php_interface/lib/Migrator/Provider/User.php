<?

namespace FourPaws\Migrator\Provider;

use Bitrix\Main\UserTable;
use Symfony\Component\HttpFoundation\Response;

class User extends ProviderAbstract
{
    /**
     * $map - однозначное отображение ['поле на сервере' => 'поле на клиенте']
     *
     * @return array
     */
    public function getMap() : array
    {
        $map = array_keys(array_filter(UserTable::getMap(), self::getScalarEntityMapFilter()));
        
        array_merge($map,
                    [
                        'UF_DISC'              => 'UF_DISC',
                        'UF_ADDR'              => 'UF_ADDR',
                        'UF_CORRECTION_BASKET' => 'UF_CORRECTION_BASKET',
                        'UF_SHOP'              => 'UF_SHOP',
                        'UF_IS_ACTUAL_EMAIL'   => 'UF_IS_ACTUAL_EMAIL',
                        'UF_IS_ACTUAL_PHONE'   => 'UF_IS_ACTUAL_PHONE',
                        'UF_ADDR_TOWN'         => 'UF_ADDR_TOWN',
                        'UF_ADDR_STREET'       => 'UF_ADDR_STREET',
                        'UF_ADDR_HOME'         => 'UF_ADDR_HOME',
                        'UF_ADDR_CORP'         => 'UF_ADDR_CORP',
                        'UF_ADDR_KVART'        => 'UF_ADDR_KVART',
                        'UF_ADDR_POD'          => 'UF_ADDR_POD',
                        'UF_ADDR_ETAG'         => 'UF_ADDR_ETAG',
                        'UF_INTERVIEW_MES'     => 'UF_INTERVIEW_MES',
                        'UF_BONUS_MES'         => 'UF_BONUS_MES',
                        'UF_SMS_MES'           => 'UF_SMS_MES',
                        'UF_EMAIL_MES'         => 'UF_EMAIL_MES',
                        'UF_GPS_MESS'          => 'UF_GPS_MESS',
                        'UF_PUSH_ACC_CHANGE'   => 'UF_PUSH_ACC_CHANGE',
                        'UF_FEEDBACK_MES'      => 'UF_FEEDBACK_MES',
                        'UF_PUSH_ORD_STAT'     => 'UF_PUSH_ORD_STAT',
                        'UF_PUSH_NEWS'         => 'UF_PUSH_NEWS',
                    ]);
        
        return array_combine($map, $map);
    }
    
    /**
     * @return string
     */
    public function getPrimary() : string
    {
        return 'ID';
    }
    
    public function save(Response $response)
    {
    
    }
}