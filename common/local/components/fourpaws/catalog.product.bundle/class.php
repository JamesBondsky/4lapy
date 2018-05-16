<?php
/**
 * Created by PhpStorm.
 * Date: 26.04.2018
 * Time: 17:50
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\Components;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use CBitrixComponent;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Bundle;
use FourPaws\Catalog\Model\BundleItem;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Helpers\WordHelper;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */

/**
 * Class CatalogDetailBundle
 * @package FourPaws\Components
 */
class CatalogDetailBundle extends CBitrixComponent
{

    protected $userService;

    /**
     * GroupSet constructor.
     *
     * @param CBitrixComponent|null $component
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        $container = Application::getInstance()->getContainer();
        $this->userService = $container->get(CurrentUserProviderInterface::class);
    }

    /**
     * @return bool|mixed|null
     */
    public function executeComponent()
    {
        /** @var Offer $currentOffer */
        $currentOffer = $this->arParams['OFFER'];
        try {
            $this->arResult['BUNDLE'] = $currentOffer->getBundle();
        } catch (\Exception $e) {
            $logger = LoggerFactory::create('productDetail');
            $logger->error($e->getMessage());
        }
        if ($this->arResult['BUNDLE'] === null) {
            return null;
        }

        $this->loadTemplateFields();
        $this->includeComponentTemplate();
        return true;
    }

    /**
     *
     */
    protected function loadTemplateFields(): void
    {
        $this->arResult['SUM'] = 0;
        $this->arResult['OLD_SUM'] = 0;
        $this->arResult['BONUS'] = 0;
        /** @var Bundle $bundle */
        $bundle = $this->arResult['BUNDLE'];
        /** @var BundleItem $item */
        $products = $bundle->getProducts();
        if (\is_array($products) && !empty($products)) {
            $percent = $this->userService->getCurrentUserBonusPercent();
            foreach ($products as $item) {
                $offer = $item->getOffer();
                $this->arResult['SUM'] += $offer->getPrice()*$item->getQuantity();
                $this->arResult['OLD_SUM'] += $offer->getOldPrice()*$item->getQuantity();
                $this->arResult['BONUS'] += $offer->getBonusCount($percent, $item->getQuantity());
            }
            $this->arResult['BONUS_FORMATTED'] = $this->formattedBonus($this->arResult['BONUS']);
        }
    }

    /**
     * @param float $bonus
     * @param int   $precision
     *
     * @return string
     */
    protected function formattedBonus(float $bonus, $precision = 0): string
    {
        $bonusText = '';

        if ($bonus <= 0) {
            return $bonusText;
        }

        if($precision > 0 ){
            $bonus = \round($bonus, $precision, \PHP_ROUND_HALF_DOWN);
            $floorBonus = \floor($bonus);
        }
        else{
            $floorBonus = $bonus = \floor($bonus);
        }

        $div = ($bonus - $floorBonus) * 100;

        return \sprintf(
            '+ %s %s',
            WordHelper::numberFormat($bonus, $precision),
            WordHelper::declension($div ?: $floorBonus, ['бонус', 'бонуса', 'бонусов'])
        );
    }
}