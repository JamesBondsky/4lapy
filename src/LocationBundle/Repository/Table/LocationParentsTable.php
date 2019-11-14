<?php

namespace FourPaws\LocationBundle\Repository\Table;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

class LocationParentsTable extends Main\Entity\DataManager
{
    public static function getTableName(): string
    {
        return '4lapy_locations_parents';
    }

    public static function getMap(): array
    {
        return array(
            'ID' => new IntegerField(
                'ID',
                [
                    'primary'      => true,
                    'autocomplete' => true,
                    'title' => 'ID местоположения',
                ]
            ),
            'PARENTS' => new StringField(
                'PARENTS',
                [
                    'required' => true,
                    'title' => 'Родительские местоположения',
                ]
            ),
        );
    }

    /**
     *
     */
    public static function deleteAll(): void
    {
        $connection = Application::getConnection();
        $connection->truncateTable(static::getTableName());
    }
}