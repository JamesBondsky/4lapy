<?php

use FourPaws\Catalog\Model\Offer;
use FourPaws\Helpers\WordHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$offers = $templateData['OFFERS'];
if(\is_array($offers) && !empty($offers)){
    foreach ($offers as $key => $offer) {
        $explode = explode('_', $key);
        $id = $explode[0];
        $quantity=$explode[1];
        /** @var Offer $offer */
        $offer = $templateData['currentOffer'];
        try {
            $bonuses = $offer->getBonuses($component->getCurrentUserService()->getCurrentUser()->getDiscount(), $quantity);
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
