<?php

namespace FourPaws\Search\Table;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;

class SearchRequestStatisticTable extends DataManager
{
    /**
     * @inheritdoc
     */
    public static function getTableName()
    {
        return '4lapy_search_request_statistic';
    }

    /**
     * @inheritdoc
     */
    public static function getMap()
    {
        return [
            'id' => new IntegerField(
                'id',
                [
                    'primary' => true,
                    'autocomplete' => true,
                ]
            ),
            'search_string' => new StringField(
                'search_string', [
                    'required' => true,
                    'title' => 'Поисковый запрос',
                ]
            ),
            'quantity' => new IntegerField(
                'quantity',
                [
                    'required' => true,
                    'title' => 'Количество поисковых запросов',
                ]
            ),
            'last_date_search' => new DateTimeField(
                'last_date_search',
                [
                    'required' => true,
                    'title' => 'Дата последнего поискового запроса',
                ]
            )
        ];
    }
}
