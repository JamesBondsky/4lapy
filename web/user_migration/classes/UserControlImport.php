<?php

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\UserTable;
use FourPaws\App\Application;
use FourPaws\External\ManzanaService;
use FourPaws\UserBundle\Repository\UserRepository;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerAwareInterface;

class UserControlImport extends UserControl implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    const LOG_NAME = 'ControlImport';
    const DISCOUNT_USER_FIELD_CODE = 'UF_DISCOUNT_CARD';

    protected $fileImportPath = false;

    protected $foundUsers = [];
    protected $needCreateUsers = [];

    protected $petsPart = [];
    protected $needCreatePets = [];
    protected $foundUsersPets = [];

    function __construct($pageSize = 1000)
    {
        $this->pageSize = intval($pageSize);
        $this->withLogName(static::LOG_NAME)->withLogType(static::LOG_NAME);
        $this->setLogger(LoggerFactory::create($this->getLogName(), $this->getLogType()));
        parent::__construct();
    }

    /**
     * @param string $file
     *
     * @return void
     */
    public function importPart($file, $offset)
    {
        $this->usersPart = $this->readFromFile($file);
        /** @var обрезаем usersPart */
        $this->usersPart = array_slice($this->usersPart, $offset, $this->pageSize, true);
        /** ищем пользаков, которые есть в базе */
        $this->foundUsers = $this->tryFindUsers();
        /** получаем пользаков, которых нет в базе */
        $needCreateUsersIDs = array_diff(array_keys($this->usersPart), array_keys($this->foundUsers));
        if (count($needCreateUsersIDs) > 0) {
            foreach ($needCreateUsersIDs as $needCeateUsersID) {
                $this->needCreateUsers[$needCeateUsersID] = $this->usersPart[$needCeateUsersID];
            }
        }

        /** создаем пользаков */
        if (count($this->needCreateUsers) > 0) {
            $this->createUsers();
            $this->createUsersPets();
        }

//        foreach ($this->usersPart as $userData) {
//            if ($userID) {
////                $this->updateUser($userID, $userData);
//            } else {
//                $this->createUser($userData);
//            }
//        }
    }

    /**
     * Возвращает количество записей в файле
     *
     * @return int
     */
    public function getUsersCntFromFile($file)
    {
        $lines = $this->readFromFile($file);
        return 1; //count($lines);
    }

    /**
     * Читает весь файл
     *
     * @return int|null
     */
    private function readFromFile($file)
    {
        $lines = [];
        $this->fileImportPath = $_SERVER['DOCUMENT_ROOT'] . static::UPLOAD_SUB_PATH . '/' . $file;

        if (!file_exists($this->fileImportPath) || !is_file($this->fileImportPath)) {
            return $lines;
        }

        $map = array_merge(static::MAPPING, [static::DISCOUNT_USER_FIELD_CODE], [static::PET_MAP]);
        $fp = fopen($this->fileImportPath, 'r');
        $rowNumber = 0;
        $row = fgetcsv($fp, filesize($this->fileImportPath), static::DELIMITER); //первую пропускаем - названия колонок
        while ($row = fgetcsv($fp, filesize($this->fileImportPath), static::DELIMITER)) {
            $userID = $row[0];
            for ($key = 0; $key < count($row); $key++) {
                if ($map[$key] == 'ID') {
                    continue;
                }
                if ($map[$key] != 'PETS') {
                    $lines[$userID][$map[$key]] = $row[$key];
                } else {
                    $lines[$userID][$map[$key]] = json_decode($row[$key], true);
                }

            }
            $rowNumber++;
        }
        fclose($fp);

        return $lines;
    }

    /**
     * Возвращает список пользователей, которые были найдены по полям EMAIL+PERSONAL_PHONE
     *
     * @return array
     */
    private function tryFindUsers()
    {
        $filter[] = [
            "LOGIC" => "OR"
        ];

        $userTmp = [];
        foreach ($this->usersPart as $userID => $userData) {
            $filter[0][] = [
                'EMAIL' => $userData['EMAIL'],
                'PERSONAL_PHONE' => $userData['PERSONAL_PHONE']
            ];
            $userTmp[$userData['EMAIL'] . $userData['PERSONAL_PHONE']] = $userID;
        }

        $select = array_merge(static::MAPPING, [static::DISCOUNT_USER_FIELD_CODE]);
        $foundUsers = [];
        $dbRes = UserTable::query()
            ->setSelect($select)
            ->setFilter($filter)
            ->exec();

        if ($dbRes->getSelectedRowsCount() > 0) {
            while ($foundUser = $dbRes->fetch()) {
                $userIDFromOldSite = $userTmp[$foundUser['EMAIL'] . $foundUser['PERSONAL_PHONE']];
                $foundUsers[$userIDFromOldSite] = $foundUser;
            }
        }

        return $foundUsers;
    }

    /**
     * Добавляет нового пользователя, которого мы не нашли в базе
     */
    private function createUsers()
    {
        global $DB;
        $cUser = new CUser;
        /** @var UserRepository $userRepo */
        $userRepo = Application::getInstance()->getContainer()->get(UserRepository::class);
        /** @var ManzanaService $manzanaService */
        $manzanaService = Application::getInstance()->getContainer()->get('manzana.service');
        foreach ($this->needCreateUsers as $userOldId => &$userData) {
            try {
                $arPets = [];
                if (is_array($userData[static::PET_MAP])) {
                    $arPets = $userData[static::PET_MAP];
                    unset($this->needCreateUsers[$key][static::PET_MAP]);
                }
                $userID = $cUser->add($userData);
                if ($userID) {
                    /** обновляем пароль на хэш */
                    $updateRes = $DB->Query('UPDATE FROM b_user WHERE ID=' . $userID . ' SET PASSWORD=' . $userData['PASSWORD']);
                    if ($updateRes) {
                        $this->log()->notice('Пользователь успешно создан', array_merge(['new_user_id' => $userID], $userData));
                        /** Добавляем в массив пользователя, если у него есть питомцы */
                        if (count($arPets) > 0) {
                            $this->needCreatePets[$userID] = $arPets;
                        }
                        /** Добавляем задачу в рэббит на обновление заказов */
                        $this->addManzanaTask($userID, $userRepo, $manzanaService);
                    } else {
                        UserTable::delete($userID);
                        $this->log()->error('Не удалось обновить хэш пароля пользователя (удален): ', $userData);
                    }
                } else {
                    $this->log()->error('Невозможно создать пользователя с данными или обновить пароль: ' . ($cUser->LAST_ERROR) ?: \implode('. ', $updateRes->getErrorMessages()), $userData);
                    /** исключение, что такой юзер с тамик e-mail`ом есть, обработка питомцев */
                    if (mb_strpos($cUser->LAST_ERROR, 'Пользователь с таким e-mail') !== false) {
                        /** получаем пользователя */
                        $curUser = UserTable::query()
                            ->setSelect(['ID'])
                            ->setFilter(['EMAIL' => $userData['EMAIL']])
                            ->exec()->fetch();
                        /** получаем его питомцев */
                    }
                }
            } catch (\Exception $e) {
                $this->log()->error('Невозможно создать пользователя с данными', $userData);
            }
        }
    }

    /**
     * Добавляем задачу в рэббит на обновление заказов
     *
     * @param $userID
     * @param UserRepository $userRepo
     * @param ManzanaService $manzanaService
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function addManzanaTask($userID, UserRepository $userRepo, ManzanaService $manzanaService)
    {
        $curUser = $userRepo->find($userID);
        if ($curUser) {
            $manzanaService->importUserOrdersAsync($curUser);
        }
    }

    /**
     * Добавляет питомцев пользователей
     *
     * @param $userData
     * @return bool|int
     */
    private function createUsersPets()
    {
        return true;
    }
}