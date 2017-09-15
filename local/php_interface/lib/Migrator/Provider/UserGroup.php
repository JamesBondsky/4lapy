<?

namespace FourPaws\Migrator\Provider;

use Bitrix\Main\UserGroupTable;
use Symfony\Component\HttpFoundation\Response;

class UserGroup extends ProviderAbstract
{
    /**
     * @return array
     */
    public function getMap() : array
    {
        $map = array_keys(array_filter(UserGroupTable::getMap(), self::getScalarEntityMapFilter()));

        return array_combine($map, $map);
    }

    /**
     * @return string
     */
    public function getPrimary() : string
    {
        return 'ID';
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function save(Response $response)
    {
        $lastTimestamp = null;

        foreach ($this->parseResponse($response) as $item) {
            try {
                $this->

                $result->setResult(true);
            } catch (\Exception $e) {
                $result->setResult(false);
            }
        }
    }
}