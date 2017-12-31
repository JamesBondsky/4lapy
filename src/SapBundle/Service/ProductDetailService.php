<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Service;

use Bitrix\Main\Application;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Dto\In\Offers\Materials;

class ProductDetailService
{
    /**
     * @var \Bitrix\Main\DB\Connection
     */
    protected $connection;

    public function __construct()
    {
        $this->connection = Application::getConnection();
    }

    public function processMaterials(Materials $materials)
    {
        /**
         * @todo Обработка ошибок
         * @todo Транзакционность импорта в рамках одного sku
         */
        foreach ($materials->getMaterials() as $material) {
            $this->processMaterial($material);
        }
    }

    public function processMaterial(Material $material)
    {
        $this->connection->startTransaction();
        
        $this->connection->commitTransaction();
    }
}
