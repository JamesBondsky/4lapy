<?php

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use FourPaws\App\Application;
use FourPaws\External\ManzanaService;
use FourPaws\PersonalBundle\Entity\Pet;
use FourPaws\PersonalBundle\Service\PetService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Repository\UserRepository;
use Psr\Log\LoggerAwareInterface;

class UserControlImport extends UserControl implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    const LOG_NAME = 'ControlImport';
    const DISCOUNT_USER_FIELD_CODE = 'UF_DISCOUNT_CARD';
    const DISCOUNT_OLD_USER_FIELD_CODE = 'UF_DISC';
    const CACHE_TIME = 86400;
    const LID = 's1';
    const LANGUAGE_ID = 'ru';
    const USER_GROUPS = [
        6, //register users
        42 //apply basket rules
    ];

    /** коды с нового сайта => название на старом сайте */
    const CATEGORIES_FROM_OLD_SITE = [
        'ryby'    => 'Рыбка',
        'gryzuny' => 'Грызун',
        'koshki'  => 'Кошка',
        'prochee' => 'Другое',
        'sobaki'  => 'Собака',
        'ptitsy'  => 'Птичка'
    ];

    const UPDATE_USER_FIELDS = [
        'NAME',
        'LAST_NAME',
        'SECOND_NAME',
        'PERSONAL_BIRTHDAY',
        'PERSONAL_GENDER'
    ];

    /**
     * @var string|bool $fileImportPath
     *
     * путь к файлу импорта
     */
    protected $fileImportPath = false;

    /**
     * @var array $foundUsers
     *
     * key = OldSiteUserId
     * value = array of user data from new site
     */
    protected $foundUsers = [];

    /**
     * @var array $foundUsersPets
     *
     * key = newSiteUserId
     * value = array of user pets data from new site
     */
    protected $foundUsersPets = [];

    /**
     * @var array $needCreateUsers
     *
     * key = OldSiteUserId
     * value = array of user data from old site
     */
    protected $needCreateUsers = [];

    protected $needUpdatePets = [];

    protected $forWho = null;
    protected $gender = null;
    protected $breeds = null;

    /**
     * Statistics vars
     */
    protected $usersFound = 0;
    protected $usersAdded = 0;
    protected $petsFound = 0;
    protected $petsAdded = 0;
    protected $totalPets = 0;

    /**
     * UserControlImport constructor.
     * @param int $pageSize
     * @param $usersFound
     * @param $usersAdded
     * @param $petsFound
     * @param $petsAdded
     */
    function __construct($pageSize = 1000, $usersAdded = 0, $usersFound = 0, $petsAdded = 0, $petsFound = 0, $totalPets = 0)
    {
        $this->petImagesPath = $_SERVER['DOCUMENT_ROOT'] . static::UPLOAD_SUB_PATH . '/' . $this->petImagesFolder;
        $this->usersFound = $usersFound;
        $this->usersAdded = $usersAdded;
        $this->petsFound = $petsFound;
        $this->petsAdded = $petsAdded;
        $this->totalPets = $totalPets;

        $this->pageSize = intval($pageSize);
        $this->withLogName(static::LOG_NAME)->withLogType(static::LOG_NAME);
        $this->setLogger(LoggerFactory::create($this->getLogName(), $this->getLogType()));
        $this->getForWho();
        $this->getGender();
        $this->getBreed();
        parent::__construct();
    }

    protected function getForWho()
    {
        /** Выбираем и кэшируем все разделы */
        $cache = new CPHPCache();
        $cacheID = $cachePath = 'UserControlForWho';
        if ($cache->InitCache(static::CACHE_TIME, $cacheID, $cachePath)) {
            $res = $cache->GetVars();
            if (is_array($res[$cacheID]) && (count($res[$cacheID]) > 0)) {
                $this->forWho = $res[$cacheID];
            }
        }

        if (!is_array($this->forWho)) {
            $forWhoDb = Application::getHlBlockDataManager('bx.hlblock.forwho')::getList();
            while ($forWho = $forWhoDb->fetch()) {
                $oldCatName = mb_strtolower(static::CATEGORIES_FROM_OLD_SITE[$forWho['UF_CODE']]);
                if ($oldCatName) {
                    $this->forWho[$oldCatName] = $forWho['ID'];
                }
            }
            $cache->StartDataCache(static::CACHE_TIME, $cacheID, $cachePath, ['UserControlForWho' => $this->forWho]);
            $cache->EndDataCache(['UserControlForWho' => $this->forWho]);
        }
    }

    protected function getGender()
    {
        /** Выбираем и кэшируем все разделы */
        $cache = new CPHPCache();
        $cacheID = $cachePath = 'UserControlGender';
        if ($cache->InitCache(static::CACHE_TIME, $cacheID, $cachePath)) {
            $res = $cache->GetVars();
            if (is_array($res[$cacheID]) && (count($res[$cacheID]) > 0)) {
                $this->gender = $res[$cacheID];
            }
        }

        if (!is_array($this->gender)) {
            $obGender = new CUserFieldEnum;
            /** @var CDBResult $dbGender */
            $dbGender = $obGender->GetList([], ['USER_FIELD_NAME' => 'UF_GENDER']);
            while ($gender = $dbGender->Fetch()) {
                $this->gender[mb_strtolower($gender['VALUE'])] = $gender['ID'];
            }
            $cache->StartDataCache(static::CACHE_TIME, $cacheID, $cachePath, ['UserControlGender' => $this->gender]);
            $cache->EndDataCache(['UserControlGender' => $this->gender]);
        }
    }

    protected function getBreed()
    {
        /** Выбираем и кэшируем все разделы */
        $cache = new CPHPCache();
        $cacheID = $cachePath = 'UserControlBreed';
        if ($cache->InitCache(static::CACHE_TIME, $cacheID, $cachePath)) {
            $res = $cache->GetVars();
            if (is_array($res[$cacheID]) && (count($res[$cacheID]) > 0)) {
                $this->breeds = $res[$cacheID];
            }
        }

        if (!is_array($this->breeds)) {
            $dbBreeds = Application::getHlBlockDataManager('bx.hlblock.petbreed')::getList();
            while ($breed = $dbBreeds->fetch()) {
                $this->breeds[mb_strtolower($breed['UF_NAME'])] = $breed['ID'];
            }
            $cache->StartDataCache(static::CACHE_TIME, $cacheID, $cachePath, ['UserControlBreed' => $this->breeds]);
            $cache->EndDataCache(['UserControlBreed' => $this->breeds]);
        }
    }

    /**
     * @param string $file
     * @param string $offset
     *
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function importPart($file, $offset)
    {
        $this->usersPart = $this->readFromFile($file);
        /** обрезаем usersPart */
        $this->usersPart = array_slice($this->usersPart, $offset * $this->pageSize, $this->pageSize, true);
        /** ищем пользаков, которые есть в базе */
        $this->foundUsers = $this->tryFindUsers();
        $this->usersFound += count($this->foundUsers);
        $this->log()->notice('Найдено ' . count($this->foundUsers) . ' пользователей из ' . $this->pageSize);
        /** получаем пользаков, которых нет в базе */
        $needCreateUsersIDs = array_diff(array_keys($this->usersPart), array_keys($this->foundUsers));
        if (!empty($needCreateUsersIDs)) {
            foreach ($needCreateUsersIDs as $needCeateUsersID) {
                $this->needCreateUsers[$needCeateUsersID] = $this->usersPart[$needCeateUsersID];
            }
        }

        $this->updateFoundUsersInfo();

        /** Считаем количество питомцев в части */
        foreach ($this->usersPart as $user) {
            $this->totalPets += (!empty($user[static::PET_MAP])) ? count($user[static::PET_MAP]) : 0;
        }

        /** создаем пользаков, внутри так же для каждого создаем питомцев */
        if (!empty($this->needCreateUsers)) {
            $this->createUsers();
        }

        $this->getAllPetsFromFile();
        $this->getAllPetsFromNewSite();
        $this->addNotFoundUserPets();

        return [
            'users_added' => $this->usersAdded,
            'users_found' => $this->usersFound,
            'pets_added'  => $this->petsAdded,
            'pets_found'  => $this->petsFound,
            'total_pets'  => $this->totalPets
        ];
    }

    /**
     * Выбираем всех питомцев пользователей из файла
     */
    protected function getAllPetsFromFile()
    {
        foreach ($this->foundUsers as $userOldId => $curUserFound) {
            $pets = $this->usersPart[$userOldId][static::PET_MAP];
            if (is_array($pets) && !empty($pets)) {
                $this->needUpdatePets[$curUserFound['ID']] = $pets;
            }
        }
    }

    /**
     * Выбираем всех существующих питомцев пользователей, которые есть на новом сайте на новом сайте
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getAllPetsFromNewSite()
    {
        /** @var PetService $petService */
        $petService = Application::getInstance()->getContainer()->get('pet.service');
        /** @var array $userIDs - массив айдишников пользаков с нового сайта */
        $userIDs = array_keys($this->needUpdatePets);
        if (!empty($userIDs)) {
            $petsCollection = $petService->getUsersPets($userIDs);
            /** @var Pet $pet */
            foreach ($petsCollection as $pet) {
                $this->foundUsersPets[$pet->getUserId()][] = $pet->getName();
            }
        }
    }

    protected function addNotFoundUserPets()
    {
        foreach ($this->needUpdatePets as $userID => $petsFromOldSite) {
            $petsDiff = [];
            $petsFormNewSite = ($this->foundUsersPets[$userID]) ?: [];
            if (empty($petsFormNewSite)) {
                $petsDiff = $petsFromOldSite;
            } else {
                foreach ($petsFromOldSite as $pet) {
                    if (!in_array($pet['NAME'], $petsFormNewSite)) {
                        $petsDiff[] = $pet;
                    } else {
                        $this->petsFound++;
                        $this->log()->notice('Питомец"' . $pet['NAME'] . '" пользователя ' . $userID . ' уже есть в базе');
                    }
                }
            }
            if (!empty($petsDiff)) {
                $this->createUserPets($userID, $petsDiff);
            }
        }
    }

    /**
     * Возвращает количество записей в файле
     *
     * @return int
     */
    public function getUsersCntFromFile($file)
    {
        $lines = $this->readFromFile($file);
        return count($lines);
    }

    /**
     * Читает весь файл
     *
     * @param $file
     * @return array
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
        fgetcsv($fp, filesize($this->fileImportPath), static::DELIMITER); //первую пропускаем - названия колонок
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
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function tryFindUsers()
    {
        $filter[] = [
            'LOGIC' => 'OR'
        ];

        $userTmp = [];
        foreach ($this->usersPart as $userIDFromOldSite => &$userData) {
            if ($userData['EMAIL'][0] == 7) {
                $userData['EMAIL'] = substr($userData['EMAIL'], 1);
            }
            if ($userData['PERSONAL_PHONE'][0] == 7) {
                $userData['PERSONAL_PHONE'] = substr($userData['PERSONAL_PHONE'], 1);
            }
            if ($userData['LOGIN'][0] == 7) {
                $userData['LOGIN'] = substr($userData['LOGIN'], 1);
            }
            if ($userData['EMAIL']) {
                $filter[0][] = [
                    'EMAIL'          => $userData['EMAIL'],
                    'PERSONAL_PHONE' => $userData['PERSONAL_PHONE']
                ];
                $filter[0][] = [
                    'LOGIN' => $userData['LOGIN'],
                ];
                $userTmp[$userData['EMAIL'] . $userData['PERSONAL_PHONE']] = $userIDFromOldSite;
                $userTmp[$userData['LOGIN']] = $userIDFromOldSite;
            } else {
                $filter[0][] = [
                    'LOGIN' => $userData['LOGIN'],
                ];
                if ($userData['PERSONAL_PHONE']) {
                    $filter[0][] = [
                        'PERSONAL_PHONE' => $userData['PERSONAL_PHONE'],
                    ];
                }
                $userTmp[$userData['LOGIN']] = $userIDFromOldSite;
                $userTmp[$userData['PERSONAL_PHONE']] = $userIDFromOldSite;
            }

        }

        unset($userIDFromOldSite);

        $select = array_merge(static::MAPPING, [static::DISCOUNT_USER_FIELD_CODE]);
        $foundUsers = [];
        $dbRes = UserTable::query()
            ->setSelect($select)
            ->setFilter($filter)
            ->exec();

        if ($dbRes->getSelectedRowsCount() > 0) {
            while ($foundUser = $dbRes->fetch()) {
                $userIDFromOldSite = $userTmp[$foundUser['EMAIL'] . $foundUser['PERSONAL_PHONE']];
                if (!$userIDFromOldSite) {
                    $userIDFromOldSite = $userTmp[$foundUser['LOGIN']];
                }
                if (!$userIDFromOldSite) {
                    $userIDFromOldSite = $userTmp[$foundUser['PERSONAL_PHONE']];
                }
                if (!$userIDFromOldSite) {
                    continue;
                }
                $foundUsers[$userIDFromOldSite] = $foundUser;
                unset($userIDFromOldSite);
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
        foreach ($this->needCreateUsers as $userOldId => &$userData) {
            try {
                $arPets = $userData[static::PET_MAP] ?: [];
                unset($this->needCreateUsers[$userOldId][static::PET_MAP]);
                $userData['LID'] = static::LID;
                $userData['LANGUAGE_ID'] = static::LANGUAGE_ID;
                $userData['TIMESTAMP_X'] = new DateTime();
                if ($userData['PERSONAL_BIRTHDAY'] != null && $userData['PERSONAL_BIRTHDAY'] != '') {
                    $personalBirthdayStr = $userData['PERSONAL_BIRTHDAY'];
                    $userData['PERSONAL_BIRTHDAY'] = new DateTime($userData['PERSONAL_BIRTHDAY'], 'Y-m-d H-i-s');
                } else {
                    unset($userData['PERSONAL_BIRTHDAY']);
                }

                $userData['GROUP_ID'] = static::USER_GROUPS;

                //@fastorder.ru && @register.phone
                if (strpos($userData['EMAIL'], '@fastorder.ru') !== false || strpos($userData['EMAIL'], '@register.phone') !== false) {
                    $userData['EMAIL'] = '';
                }

                /** удаляем 7ку из логина/email`a и телефона */
                if ($userData['LOGIN'][0] == 7) {
                    $userData['LOGIN'] = substr($userData['LOGIN'], 1);
                }
                if ($userData['EMAIL'][0] == 7) {
                    $userData['EMAIL'] = substr($userData['EMAIL'], 1);
                }
                if ($userData['PERSONAL_PHONE'][0] == 7) {
                    $userData['PERSONAL_PHONE'] = substr($userData['PERSONAL_PHONE'], 1);
                }

                /** добавляем пользователя */
                $userID = $cUser->add($userData);
                if ($userID) {
                    /** обновляем пароль на хэш */
                    try {
                        $updateRes = $DB->Query('UPDATE b_user SET PASSWORD = "' . $userData['PASSWORD'] . '" WHERE ID = ' . $userID);
                        if (!$updateRes->result) {
                            throw new Exception();
                        }
                        $this->log()->notice('Пользователь успешно создан', array_merge(['new_user_id' => $userID], $userData));
                        $this->usersAdded++;
                    } catch (Exception $e) {
                        UserTable::delete($userID);
                        $this->log()->error('Не удалось обновить хэш пароля пользователя (удален): ' . $e->getMessage(), $userData);
                    }
                    /** Создаем питомцев для нового пользователя. если они есть */
                    if (!empty($arPets)) {
                        $this->createUserPets($userID, $arPets);
                    }
                    /** Добавляем задачу в рэббит на обновление заказов */
                    $this->addManzanaTask($userID);
                } else {
                    $userData['PERSONAL_BIRTHDAY'] = $personalBirthdayStr;
                    $curUserFound = null;
                    $this->log()->error('Невозможно создать пользователя с данными или обновить пароль: ' . ($cUser->LAST_ERROR), $userData);
                    /** исключение, что такой юзер с тамик e-mail`ом есть, обработка питомцев */
                    if (mb_strpos($cUser->LAST_ERROR, 'Пользователь с таким e-mail') !== false) {
                        /** получаем пользователя */
                        $curUserFound = UserTable::query()
                            ->setSelect(array_merge(['ID'], static::MAPPING, [static::DISCOUNT_USER_FIELD_CODE]))
                            ->setFilter(['EMAIL' => $userData['EMAIL']])
                            ->exec()->fetch();
                        $this->log()->notice('Пользователь ' . $userOldId . ' найден по e-mail', $userData);
                        $this->updateUserInfo($userData, $curUserFound);
                    } elseif (mb_strpos($cUser->LAST_ERROR, 'Пользователь с логином') !== false) {
                        /** получаем пользователя */
                        $curUserFound = UserTable::query()
                            ->setSelect(array_merge(['ID'], static::MAPPING, [static::DISCOUNT_USER_FIELD_CODE]))
                            ->setFilter(['LOGIN' => $userData['LOGIN']])
                            ->exec()->fetch();
                        $this->log()->notice('Пользователь ' . $userOldId . ' найден по логину', $userData);
                        $this->updateUserInfo($userData, $curUserFound);
                    }
                    /** Если мы нашли пользователя по email, либо по логину (точное совпадение), то добавляем его питомцев в апдейт массив */
                    if ($curUserFound['ID'] && !empty($arPets)) {
                        $this->foundUsers[$userOldId] = $curUserFound;
                        $this->needUpdatePets[$curUserFound['ID']] = $arPets;
                    }
                    if ($curUserFound['ID']) {
                        $this->usersFound++;
                    }
                }
            } catch (Exception $e) {
                $this->log()->error('Невозможно создать пользователя с данными: ' . $e->getMessage(), $userData);
            }
        }
    }


    /**
     * @param $userID
     * @param $arPets
     */
    private function createUserPets($userID, $arPets)
    {
        /** @var PetService $petService */
        $petService = Application::getInstance()->getContainer()->get('pet.service');
        foreach ($arPets as $pet) {
            $arFile = null;
            if (isset($pet['PET_IMAGE_FILE_NAME'][0])) {
                $fileName = $pet['PET_IMAGE_FILE_NAME'][0];
                $arFile = CFile::MakeFileArray($this->petImagesPath . '/' . $fileName);
            }
            $category = $this->forWho[mb_strtolower($pet['CATEGORY'])];
            $breed = ($pet['BREED']) ?: $pet['OTHER_BREED'];
            $breedID = $this->breeds[mb_strtolower($breed)];

            $data = [
                'UF_USER_ID' => $userID,
                'UF_NAME'    => $pet['NAME']
            ];

            if ($category) {
                $data['UF_TYPE'] = $category;
            }

            if ($arFile) {
                $data['UF_PHOTO'] = 1;
                $data['UF_PHOTO_TMP'] = $arFile;
            }

            if ($breedID) {
                $data['UF_BREED'] = '';
                $data['UF_BREED_ID'] = $breedID;
            } elseif ($breed) {
                $data['UF_BREED'] = $breed;
            }

            if ($pet['BIRTHDAY']) {
                $data['UF_BIRTHDAY'] = $pet['BIRTHDAY'];
            }

            $gender = $this->gender[mb_strtolower($pet['GENDER'])];
            if ($gender) {
                $data['UF_GENDER'] = $gender;
            }

            try {
                $petService->add($data);
                $this->log()->notice('Питомец успешно создан для пользователя ' . $userID, $pet);
                $this->petsAdded++;
            } catch (Exception $e) {
                $this->log()->error('Ошибка создания питомца:' . $e->getMessage() . '[' . $e->getCode() . ']', $arPets);
            }
        }
    }

    private function updateFoundUsersInfo()
    {
        foreach ($this->foundUsers as $userOldId => $foundUser) {
            $oldUser = $this->usersPart[$userOldId];
            $this->updateUserInfo($oldUser, $foundUser);
        }
    }

    /**
     * @param $oldUser
     * @param $foundUser
     */
    private function updateUserInfo($oldUser, $foundUser)
    {
        $updateFields = [];
        foreach (static::UPDATE_USER_FIELDS as $field) {
            if ($foundUser[$field] == '' && $oldUser[$field]) {
                if ($field == 'PERSONAL_BIRTHDAY') {
                    try {
                        $date = new DateTime($oldUser[$field], 'Y-m-d H-i-s');
                        $updateFields[$field] = $date;
                    } catch (ObjectException $e) {
                        continue;
                    }
                } else {
                    $updateFields[$field] = $oldUser[$field];
                }
            }
        }

        if ($foundUser[static::DISCOUNT_USER_FIELD_CODE] == '' && $oldUser[static::DISCOUNT_OLD_USER_FIELD_CODE]) {
            $updateFields[static::DISCOUNT_USER_FIELD_CODE] = $oldUser[static::DISCOUNT_OLD_USER_FIELD_CODE];
        }

        if (count($updateFields)) {
            $cUser = new CUser;
            $res = $cUser->Update($foundUser['ID'], $updateFields);
            $this->addManzanaTask($foundUser['ID']);
            if (!$res) {
                $this->log()->error('Невозможно обновить данные пользователя ' . $foundUser['ID'] . ' ' . $cUser->LAST_ERROR, $updateFields);
            } else {
                $this->log()->notice('Данные пользователя ' . $foundUser['ID'] . ' успешно обновлены', $updateFields);
            }
        }
    }

    /**
     * @param $userID
     */
    private function addManzanaTask($userID)
    {
        /** @var UserRepository $userRepo */
        $userRepo = Application::getInstance()->getContainer()->get(UserRepository::class);
        /** @var ManzanaService $manzanaService */
        $manzanaService = Application::getInstance()->getContainer()->get('manzana.service');
        /** Получаем объект пользака */
        try {
            $curUser = $userRepo->find($userID);
            if ($curUser) {
                $manzanaService->importUserOrdersAsync($curUser);
            }
        } catch (SystemException| ArgumentException| ObjectPropertyException $e) {
        }
    }
}