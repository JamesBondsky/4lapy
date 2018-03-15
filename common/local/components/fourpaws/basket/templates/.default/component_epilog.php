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
            $bonuses = $offer->getBonuses($discount, $quantity);
        } catch (\Exception $e) {
            $bonuses = 0;
        }
        if($bonuses > 0){
            $bonuses = round($bonuses, 2, PHP_ROUND_HALF_DOWN);
            $ost = $bonuses - floor($bonuses) * 100;
            $bonus = '+'.$bonuses.' '.WordHelper::declension($ost > 0 ? $ost : floor($bonuses),
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
