<?

namespace FourPaws\Migrator\Client;

use FourPaws\Migrator\Provider\UserGroup as UserGroupProvider;
use FourPaws\Migrator\Provider\User as UserProvider;

class UserPull extends ClientPullAbstract
{
    
    public function getBaseClientList() : array {
        return [
            new UserGroup(new UserGroupProvider(), ['force' => $this->force]),
        ];
    }
    
    public function getClientList() : array {
        return [
            new User(new UserProvider(), ['limit' => $this->limit, 'force' => $this->force]),
        ];
    }
}