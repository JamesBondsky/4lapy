<?php

namespace Sprint\Migration;


use Bitrix\Main\Application;
use FourPaws\Search\Table\SearchRequestStatisticTable;

class SearchRequestStatisticTableRefactoring20190312111923 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "Перевод всех вводимых данных в нижний регистр + пересчет таблицы";

    protected $backupFilePath = '/local/php_interface/migration_sources/4lapy_search_request_statistic.backup';

    protected $rows = [];

    public function up()
    {
        $this->backupFilePath = $_SERVER['DOCUMENT_ROOT'] . $this->backupFilePath;

        $statisticDb = SearchRequestStatisticTable::GetList();

        while ($row = $statisticDb->Fetch()) {
            $searchString = mb_strtolower($row['search_string']);
            if (!isset($this->rows[$searchString])) {
                $this->rows[$searchString] = [
                    'search_string' => $searchString,
                    'quantity' => $row['quantity'],
                    'last_date_search' => $row['last_date_search'],
                ];
            } else {
                $this->rows[$searchString]['quantity'] += $row['quantity'];
                if ($this->rows[$searchString]['last_date_search'] < $row['last_date_search']) {
                    $this->rows[$searchString]['last_date_search'] = $row['last_date_search'];
                }
            }
        }

        $rowsStr = '';
        foreach ($this->rows as $row) {
            $rowsStr .= implode('|||', $row) . "\r\n";
        }
        file_put_contents($this->backupFilePath, $rowsStr);

        Application::getConnection()->query('TRUNCATE TABLE `4lapy_search_request_statistic`;');

        foreach ($this->rows as $row) {
            SearchRequestStatisticTable::Add($row);
        }

        return true;
    }

    public function down()
    {
        //empty
    }

}
