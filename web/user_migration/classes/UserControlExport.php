<?

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class UserControlExport extends UserControl
{
    const FOLDER_CHMOD = 0775;
    const PETS_IBLOCK_CODE = 'user_pets';
    const PETS_BREEDS_IBLOCK_CODE = 'kinds';
    const DATE_REGISTER_FROM = '2018-05-01 00-00-00';
    const TIME_REGISTER_FROM = '00-00-00';
    const TIME_REGISTER_TO = '23-59-59';
    const DISCOUNT_USER_FIELD_CODE = 'UF_DISC';
    const PET_MAP = 'PETS';

    /** @var DateTime $dateRegisterFrom */
    protected $dateRegisterFrom;
    /** @var DateTime $dateRegisterTo */
    protected $dateRegisterTo;

    protected $fileExportPath = false;

    function __construct($pageSize = 1000, $dateRegisterFrom = '2018-05-01', $dateRegisterTo = null)
    {
        Loader::IncludeModule('iblock');
        $this->pageSize = intval($pageSize);
        try {
            $this->dateRegisterFrom = new DateTime($dateRegisterFrom . ' ' . static::TIME_REGISTER_FROM, static::DATE_TIME_FORMAT);
        } catch (ObjectException $e) {
            $this->dateRegisterFrom = new DateTime(static::DATE_REGISTER_FROM, static::DATE_TIME_FORMAT);
        }

        if ($dateRegisterTo) {
            try {
                $this->dateRegisterTo = new DateTime($dateRegisterTo . ' ' . static::TIME_REGISTER_TO, static::DATE_TIME_FORMAT);
            } catch (ObjectException $e) {
                $this->dateRegisterTo = null;
            }
        }

        if ($dateRegisterTo != null && $this->dateRegisterTo < $this->dateRegisterFrom) {
            $this->dateRegisterTo = new DateTime();
        }

        parent::__construct();
    }

    /**
     *
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getUsersCnt()
    {
        $select = ['CNT'];
        $filter = [
            '>=DATE_REGISTER' => $this->dateRegisterFrom
        ];

        if ($this->dateRegisterTo) {
            $filter['<=DATE_REGISTER'] = $this->dateRegisterTo;
        }

        $usersCnt = UserTable::query()
            ->setSelect($select)
            ->setFilter($filter)
            ->registerRuntimeField(
                'CNT',
                [
                    'data_type'  => 'integer',
                    'expression' => [
                        'count(%s)',
                        'ID'
                    ]
                ]
            )
            ->exec()
            ->fetch();

        /** Выбираем айди инфоблока Питомцев */
        $res = CIBlock::GetList([], ['TYPE' => 'content', 'CODE' => static::PETS_IBLOCK_CODE], false);
        while ($arRes = $res->Fetch()) {
            $petIblockId = $arRes['ID'];
        }

        return [
            'CNT'            => $usersCnt['CNT'],
            'PETS_IBLOCK_ID' => $petIblockId
        ];
    }

    /**
     * @param $file
     * @param $offset
     * @param null $id
     * @param null $petsIblockId
     * @return false|string
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function exportPart($file, $offset, $id = null, $petsIblockId = null)
    {
        $id = $id ?: 0;

        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . static::UPLOAD_SUB_PATH)) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . static::UPLOAD_SUB_PATH, static::FOLDER_CHMOD);
        }

        $this->petImagesPath = $_SERVER['DOCUMENT_ROOT'] . static::UPLOAD_SUB_PATH . '/' . $this->petImagesFolder;
        if (!is_dir($this->petImagesPath)) {
            mkdir($this->petImagesPath, static::FOLDER_CHMOD);
        } elseif (is_dir($this->petImagesPath) && $offset == 0) {
            rmdir($this->petImagesPath);
            mkdir($this->petImagesPath, static::FOLDER_CHMOD);
        }

        $fileName = $this->dateRegisterFrom->format('d.m.Y') . '-' .
            (($this->dateRegisterTo instanceof DateTime) ? $this->dateRegisterTo->format('d.m.Y') : (new DateTime)->format('d.m.Y')) .
            '-' . $file;
        $this->fileExportPath = $_SERVER['DOCUMENT_ROOT'] . static::UPLOAD_SUB_PATH . '/' . $fileName;

        if ($offset == 0 && file_exists($this->fileExportPath)) {
            unlink($this->fileExportPath);
        }

        /**
         * селект для запроса
         */
        $select = array_merge(static::MAPPING, [static::DISCOUNT_USER_FIELD_CODE]);

        /**
         * формируем название колонок
         */
        if ($offset == 0) {
            $this->usersPart[] = array_merge($select, [static::PET_MAP]);
        } else {
            $this->usersPart = [];
        }

        $filter = [
            '>ID'             => $id,
            '>=DATE_REGISTER' => $this->dateRegisterFrom
        ];

        if ($this->dateRegisterTo) {
            $filter['<=DATE_REGISTER'] = $this->dateRegisterTo;
        }

        $dbUsers = UserTable::query()
            ->setSelect($select)
            ->setFilter($filter)
            ->setOrder(
                [
                    $this->sortBy => $this->orderBy
                ]
            )
            ->setLimit($this->pageSize)
            ->exec();

        $lastID = $id;
        $userIds = [];
        while ($user = $dbUsers->fetch()) {
            /** @var DateTime $registerDate */
            $registerDate = $user['DATE_REGISTER'];
            /** @var DateTime $birthDate */
            $birthDate = $user['PERSONAL_BIRTHDAY'];
            $this->usersPart[$user['ID']] = [
                'ID'                => $user['ID'],
                'NAME'              => $user['NAME'] ?: '',
                'SECOND_NAME'       => $user['SECOND_NAME'] ?: '',
                'LAST_NAME'         => $user['LAST_NAME'] ?: '',
                'EMAIL'             => $user['EMAIL'] ?: '',
                'PERSONAL_PHONE'    => $user['PERSONAL_PHONE'] ?: '',
                'LOGIN'             => $user['LOGIN'] ?: '',
                'PASSWORD'          => $user['PASSWORD'] ?: '',
                'PERSONAL_GENDER'   => $user['PERSONAL_GENDER'] ?: '',
                'PERSONAL_BIRTHDAY' => ($birthDate) ? $birthDate->format(static::DATE_TIME_FORMAT) : '',
                'DATE_REGISTER'     => $registerDate->format(static::DATE_TIME_FORMAT),
                'UF_DISCOUNT_CARD'  => $user['UF_DISCOUNT_CARD'] ?: ''
            ];

            $userIds[] = $user['ID'];
            $lastID = $user['ID'];
        }

        $this->getBreeds();
        $this->getUsersPets($petsIblockId, $userIds);
        $fileSize = $this->writeToFile();

        return json_encode([
            'last_id'   => $lastID,
            'file_size' => $fileSize
        ]);
    }

    /**
     * Выбираем питомцев пользователей
     *
     * @param $petsIblockId
     * @param array $userIds
     */
    private function getUsersPets($petsIblockId, array $userIds)
    {
        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'PROPERTY_PET_BREED.NAME',
            'PROPERTY_PET_SEX.NAME'
        ];

        $arFilter = [
            'IBLOCK_ID'        => $petsIblockId,
            'PROPERTY_USER_ID' => $userIds
        ];

        $petImagesIds = [];
        $dbPets = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while ($obPet = $dbPets->GetNextElement()) {
            $pet = $obPet->GetFields();
            $pet['PROPERTIES'] = $obPet->GetProperties();
            $petUser = $pet['PROPERTIES']['USER_ID']['VALUE'];
            if (!$petUser || $pet['NAME'] == '') {
                continue;
            }
            $this->usersPets[$petUser][$pet['ID']] = [
                'NAME'        => $pet['NAME'],
                'BIRTHDAY'    => $pet['PROPERTIES']['PET_BIRTHDAY']['VALUE'],
                'CATEGORY'    => ($pet['PROPERTIES']['PET_CATEGORY']['VALUE']) ? $this->breeds[$pet['PROPERTIES']['PET_CATEGORY']['VALUE']] : '',
                'BREED'       => ($pet['PROPERTY_PET_BREED_NAME']) ?: '',
                'OTHER_BREED' => ($pet['PROPERTIES']['PET_BREED_OTHER']['VALUE']) ?: '',
                'GENDER'      => ($pet['PROPERTY_PET_SEX_NAME']) ?: '',
            ];
            if ($pet['PROPERTIES']['PET_PHOTO']['VALUE'] !== false) {
                foreach ($pet['PROPERTIES']['PET_PHOTO']['VALUE'] as $fileID) {
                    $petImagesIds[$petUser][$pet['ID']][] = $fileID;
                }
            }
        }

        if (count($petImagesIds) > 0) {
            $this->copyPetImages($petImagesIds);
        }

        foreach ($this->usersPets as $petUser => $usersPets) {
            $arrUserPets = [];
            foreach ($usersPets as $petID => $userPet) {
                $arrUserPets[$petID] = $userPet;
            }
            $this->usersPart[$petUser]['PETS'] = json_encode($arrUserPets, JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Записываем часть данных в файл
     *
     * @return int|null
     */
    private function writeToFile()
    {
        $fp = fopen($this->fileExportPath, 'a');
        foreach ($this->usersPart as $key => $user) {
            fputcsv($fp, $user, static::DELIMITER);
        }
        fclose($fp);
        return filesize($this->fileExportPath);
    }

    /**
     * копирует все изображения в папку и мапит
     *
     * @param array $files
     */
    private function copyPetImages(array $files)
    {
        $uploadDir = \COption::GetOptionString('main', 'upload_dir', 'upload');
        $revertFileImages = [];
        foreach ($files as $userID => $petUser) {
            foreach ($petUser as $petID => $arrImage) {
                foreach ($arrImage as $key => $imageID) {
                    $revertFileImages[$imageID] = [
                        'USER_ID' => $userID,
                        'PET_ID'  => $petID
                    ];
                }
            }
        }
        $dbFiles = \CFile::GetList([], ['@ID' => implode(',', array_keys($revertFileImages))]);
        while ($file = $dbFiles->Fetch()) {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/' . $uploadDir . '/' . $file['SUBDIR'] . '/' . $file['FILE_NAME'];
            $ext = pathinfo($path)['extension'];
            $petUser = $revertFileImages[$file['ID']];
            copy($path, $this->petImagesPath . '/' . $file['ID'] . '.' . $ext);
            $this->usersPets[$petUser['USER_ID']][$petUser['PET_ID']]['PET_IMAGE_FILE_NAME'][] = $file['ID'] . '.' . $ext;
        }
    }

    /**
     * Выбирает и кэширует все породы на старом сайте
     */
    private function getBreeds()
    {
        /** Выбираем и кэшируем все разделы */
        $cache = new CPHPCache();
        $cacheTime = 86400;
        $cacheID = 'UserControlBreeds';
        $cachePath = 'UserControlBreeds';
        if ($cacheTime > 0 && $cache->InitCache($cacheTime, $cacheID, $cachePath)) {
            $res = $cache->GetVars();
            if (is_array($res[$cacheID]) && (count($res[$cacheID]) > 0)) {
                $this->breeds = $res[$cacheID];
            }
        }

        if (!is_array($this->breeds)) {
            $arSelect = [
                'ID',
                'IBLOCK_ID',
                'NAME'
            ];

            $arFilter = [
                'IBLOCK_CODE' => static::PETS_BREEDS_IBLOCK_CODE
            ];
            $dbSections = \CIBlockSection::GetList([], $arFilter, false, $arSelect);
            while ($section = $dbSections->Fetch()) {
                $this->breeds[$section['ID']] = $section['NAME'];
            }
            if ($cacheTime > 0) {
                $cache->StartDataCache($cacheTime, $cacheID, $cachePath);
                $cache->EndDataCache(["arIBlockListID" => $this->breeds]);
            }
        }
    }
}