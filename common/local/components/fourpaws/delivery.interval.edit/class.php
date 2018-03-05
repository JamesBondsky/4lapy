<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsDeliveryIntervalEditComponent extends \CBitrixComponent
{

    /** {@inheritdoc} */
    public function onPrepareComponentParams($params): array
    {
        if (empty($params['ZONES'])) {
            throw new \Exception('Delivery zones not defined');
        }

        if (empty($params['INPUT_NAME'])) {
            throw new \Exception('Input name not defined');
        }

        foreach ($params['VALUE'] as $i => $value) {
            if (!isset($params['ZONES'][$value['ZONE_CODE']])) {
                unset($params['VALUE'][$i]);
            }
        }

        foreach ($params['ZONES'] as $code => $zone) {
            $found = false;
            foreach ($params['VALUE'] as $i => $value) {
                if ($value['ZONE_CODE'] === $code) {
                    $found = true;
                    $params['VALUE'][$i]['ZONE_NAME'] = $zone['NAME'];
                    break;
                }

                if (!isset($params['VALUE'][$i]['RULES'][\FourPaws\DeliveryBundle\Entity\IntervalRule\BaseRule::TYPE_ADD_DAYS])) {
                    $params['VALUE'][$i]['RULES'][BaseRule::TYPE_ADD_DAYS] = [];
                }
            }

            if (!$found) {
                $params['VALUE'][] = [
                    'ZONE_CODE' => $code,
                    'ZONE_NAME' => $zone['NAME'],
                    'INTERVALS' => [],
                    'RULES'     => [
                        BaseRule::TYPE_ADD_DAYS => [],
                    ],
                ];
            }
        }

        return $params;
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }
}
