<? require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

require_once('../classes/UserControl.php');

$exchangeType = $_REQUEST['exchange_type'];
$pageCnt = $_REQUEST['cnt'];
$step = $_REQUEST['step'];

global $USER;

if (!$USER->IsAdmin()) {
    die('Скрипт доступен только администратору!');
}

switch ($exchangeType) {
    case 'export':
        require_once('../classes/UserControlExport.php');
        $userControl = new UserControlExport($pageCnt);
        switch ($step) {
            case 'get_pages_count':
                $response = $userControl->getUsersCnt();
                echo json_encode(
                    [
                        'CNT' => ceil($response['CNT'] / $pageCnt),
                        'PETS_IBLOCK_ID' => $response['PETS_IBLOCK_ID']
                    ]
                );
                break;
            case 'write_elements_on_page':
                $fileName = $_REQUEST['file_name'];
                if (ctype_alnum($fileName) || !preg_match('/^(?:[a-z0-9_-]|\.(?!\.))+$/iD', $fileName)) {
                    $fileName = 'users.csv';
                }
                $pageNumber = $_REQUEST['page_number'];
                $id = $_REQUEST['id'];
                $petsIblockId = $_REQUEST['pets_iblock_id'];
                echo $userControl->exportPart($fileName, $pageNumber, $id, $petsIblockId);
                break;
        }
        break;
    case 'import':
        $fileName = $_REQUEST['file_name'];
        if (ctype_alnum($fileName) || !preg_match('/^(?:[a-z0-9_-]|\.(?!\.))+$/iD', $fileName)) {
            $fileName = 'users.csv';
        }
        require_once('../classes/UserControlImport.php');
        $usersAdded = $_REQUEST['users_added'];
        $usersFound = $_REQUEST['users_found'];
        $petsAdded = $_REQUEST['pets_added'];
        $petsFound = $_REQUEST['pets_found'];
        $totalPets = $_REQUEST['total_pets'];
        $userControl = new UserControlImport($pageCnt, $usersAdded, $usersFound, $petsAdded, $petsFound, $totalPets);
        switch ($step) {
            case 'get_pages_count':
                $cnt = $userControl->getUsersCntFromFile($fileName);
                echo json_encode(
                    [
                        'cnt' => $cnt,
                        'pages' => ceil($cnt / $pageCnt)
                    ]
                );
                break;
            case 'process_elements_on_page':
                $pageNumber = $_REQUEST['page_number'];
                $result = json_encode($userControl->importPart($fileName, $pageNumber));
                echo $result;
                break;
        }
        break;
}

require($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/epilog_after.php');
