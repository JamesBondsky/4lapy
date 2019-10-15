<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Catalog\Product\CatalogProvider;
use FourPaws\App\Application;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Repository\OldAddressRepository;

class UpdateMoscowAddress20191015182204 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Обновляет код локации у Московских адресов';

    public function up()
    {
        /** @var LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');

        try {
            $dataManager = HLBlockFactory::createTableObject(OldAddressRepository::HL_NAME);
            $res = $dataManager::query()
                ->setFilter(['=UF_CITY_LOCATION' => LocationService::LOCATION_CODE_MOSCOW])
                ->setSelect(['ID', 'UF_CITY', 'UF_STREET', 'UF_HOUSE'])
                ->exec();

            while ($address = $res->Fetch()) {
                if (($this->validateField($address['UF_CITY'])) && ($this->validateField($address['UF_STREET'])) && ($this->validateField($address['UF_HOUSE']))) {
                    $strAddress = sprintf('%s, %s, %s', $address['UF_CITY'], $address['UF_STREET'], $address['UF_HOUSE']);
                    try {
                        $okato = $locationService->getDadataLocationOkato($strAddress);
                        $locations = $locationService->findLocationByExtService(LocationService::OKATO_SERVICE_CODE, $okato);

                        if (count($locations)) {
                            $location = current($locations);

                            if (($locationCode = $location['CODE']) && ($this->validateField($locationCode))) {
                                $updateResult = $dataManager::update($address['ID'], ['UF_CITY_LOCATION' => $locationCode]);
                                if ($updateResult->isSuccess()) {
                                    $this->log()->info(
                                        sprintf('Обновлен адрес: id - %s, новый UF_CITY_LOCATION - %s', $address['ID'], $locationCode)
                                    );
                                }
                            }
                        }

                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * @param $value
     * @return bool
     */
    protected function validateField($value): bool
    {
        return $value && ($value !== null) && (!empty($value)) && ($value !== '');
    }

    public function down()
    {

    }
}
