<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Elastica\Exception\ResponseException;
use FourPaws\App\Application;

class Catalog_Elasitc20171103183357 extends SprintMigrationBase
{

    /**
     * @var \FourPaws\Search\SearchService
     */
    protected $searchService;

    /**
     * Catalog_Elasitc20171103183357 constructor.
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function __construct()
    {
        parent::__construct();
        /** @noinspection SqlNoDataSourceInspection */
        $this->description = "Create index in Elasticsearch for catalog";

        $this->searchService = Application::getInstance()->getContainer()->get('search.service');

    }

    /**
     * @return bool|void
     * @throws ResponseException
     */
    public function up()
    {
        $catalogIndex = $this->searchService->getIndexHelper()->getCatalogIndex();

        try {

            //Удаляем индекс для дополнительной идемпотентности
            $catalogIndex->delete();

        } catch (ResponseException $exception) {

            $this->log()->warning(
                sprintf(
                    'Error deleting index: %s',
                    $exception->getMessage()
                )
            );

        }
        $catalogIndex->create($this->searchService->getIndexHelper()->getCatalogIndexSettings());

    }

    public function down()
    {

    }

}
