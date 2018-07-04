<?php

namespace Sprint\Migration;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\SaleBundle\Service\BasketRulesService;
use FourPaws\App\Application as PawsApplication;
use FourPaws\SapBundle\Exception\BitrixEntityProxyException;
use FourPaws\SapBundle\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class BasketRulesResave20180704143523
 * @package Sprint\Migration
 */
class BasketRulesResave20180704143523 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Пересохранение правил корзины';

    /**
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ApplicationCreateException
     *
     * @return bool
     */
    public function up(): bool
    {
        $result = true;
        try {
            PawsApplication::getInstance()
                ->getContainer()
                ->get(BasketRulesService::class)
                ->resaveAll();
        } catch (InvalidArgumentException | BitrixEntityProxyException $exception) {
            $this->log()->error('Произошла ошибка: ' . $exception->getMessage());
            $result = false;
        }
        $this->log()->info('Скидки пересохранены');
        return $result;
    }
}
