<?
namespace FourPaws\UserBundle\Repository;

use Bitrix\Main\Type\DateTime;
use Exception;
use FourPaws\UserBundle\Exception\InvalidArgumentException;
use FourPaws\UserBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Table\ManzanaOrdersImportUserTable;

class ManzanaOrdersImportUserRepository
{
    /**
     * @param int $userId
     * @throws Exception
     */
    public function addUser(int $userId): void
    {
        if ($userId <=0) {
            throw new InvalidArgumentException(__METHOD__ . '. Неверный $userId: ' . $userId);
        }

        try
        {
            $addResult = ManzanaOrdersImportUserTable::add([
                'user_id' => $userId,
                'datetime_insert' => new DateTime(),
            ]);

            if (!$addResult->isSuccess()) {
                throw new Exception( implode('. ' , $addResult->getErrorMessages()));
            }
        } catch (\Exception $e)
        {
            throw new Exception(__METHOD__ . '. Не удалось создать пользователя. userId: ' . $userId . '. exception: ' . $e->getMessage());
        }
    }

    /**
     * @param int $userId
     * @throws NotFoundException
     * @throws Exception
     */
    public function deleteUser(int $userId): void
    {
        if ($userId <=0) {
            throw new InvalidArgumentException(__METHOD__ . '. Неверный $userId: ' . $userId);
        }

        try {
            $id = ManzanaOrdersImportUserTable::query()
                ->setFilter([
                    '=user_id' => $userId,
                ])
                ->setSelect([
                    'id',
                ])
                ->setLimit(1)
                ->exec()
                ->fetch()['id'];

            if (!$id) {
                throw new NotFoundException('Не найден userId: ' . $userId);
            }

            $deleteResult = ManzanaOrdersImportUserTable::delete($id);
            if (!$deleteResult->isSuccess()) {
                throw new Exception( implode('. ' , $deleteResult->getErrorMessages()));
            }
        } catch (NotFoundException $e) {
            throw new NotFoundException(__METHOD__ . '. ' . $e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            throw new Exception(__METHOD__ . '. Не удалось удалить пользователя. userId: ' . $userId . '. exception: ' . $e->getMessage());
        }
    }

    /**
     * @param int $userId
     * @return bool
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function hasUserId(int $userId): bool
    {
        if ($userId <=0) {
            throw new InvalidArgumentException(__METHOD__ . '. Неверный $userId: ' . $userId);
        }

        $hasUserId = (bool)ManzanaOrdersImportUserTable::getCount([
            '=user_id' => $userId,
        ]);

        return $hasUserId;
    }
}