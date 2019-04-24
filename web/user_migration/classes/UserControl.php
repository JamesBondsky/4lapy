<?

abstract class UserControl
{
    const DELIMITER = "\t";
    const UPLOAD_SUB_PATH = '/upload/users';
    const MAPPING = [
        'ID',
        'NAME',
        'SECOND_NAME',
        'LAST_NAME',
        'EMAIL',
        'PERSONAL_PHONE',
        'LOGIN',
        'PASSWORD',
        'PERSONAL_GENDER',
        'PERSONAL_BIRTHDAY',
        'DATE_REGISTER',
    ];
    const PET_MAP = 'PETS';

    protected $pageSize;
    protected $sortBy = 'ID';
    protected $orderBy = 'asc';
    protected $usersPart = [];
    protected $usersPets = [];
    protected $breeds = null;
    protected $petImagesFolder = 'pet_images';
    protected $petImagesPath = false;

    function __construct()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ERROR);
    }
}