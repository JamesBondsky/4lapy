<?php

use FourPaws\Catalog\Model\Offer;
use FourPaws\Helpers\WordHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if ($arParams['IS_AJAX']) {
    return;
}
$offers = $templateData['OFFERS'];
if(\is_array($offers) && !empty($offers)){
    $discount = $component->getCurrentUserService()->getDiscount();
    foreach ($offers as $key => $offer) {
        if(!($offer instanceof Offer)){
            if(\is_array($offer) && !empty($offer)){
                $offer = $component->getOffer($offer['ID']);
                if(!($offer instanceof Offer)){
                    continue;
                }
            }
            else{
                continue;
            }
        }
        $explode = explode('_', $key);
        $id = $explode[0];
        $quantity=(int)$explode[1];
        /** @var Offer $offer */
        try {
            $bonus = $offer->getBonuses($discount, $quantity);
        } catch (\Exception $e) {
            $bonus = 0;
        }
        if($bonus > 0){
            $bonus = round($bonus, 2, PHP_ROUND_HALF_DOWN);
            $ost = $bonus - floor($bonus) * 100;
            $bonus = '+'.WordHelper::numberFormat($bonus).' '.WordHelper::declension($ost > 0 ? $ost : floor($bonus),
                    [
                        'бонус',
                        'бонуса',
                        'бонусов',
                    ])?>
            <script type="text/javascript">
                $(function(){
                    $('.js-bonus-<?=$offer->getId()?>').html('<?=$bonus?>');
                });
            </script>
        <?php }
    }
}
