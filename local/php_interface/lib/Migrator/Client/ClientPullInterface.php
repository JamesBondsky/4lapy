<?

namespace FourPaws\Migrator\Client;

interface ClientPullInterface extends Saveable
{
    /**
     * Базовые клиенты. Без этих сущностей бессмысленно забирать остальное. Для них отключаем лимит.
     *
     * @return array
     */
    public function getBaseClientList() : array;
    
    /**
     * Все остальные клиенты
     *
     * @return array
     */
    public function getClientList() : array;
}