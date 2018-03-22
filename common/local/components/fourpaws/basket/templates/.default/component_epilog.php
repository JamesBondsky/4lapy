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
    $userDiscount = $component->getCurrentUserService()->getDiscount();
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
        $bonus = $offer->getBonusFormattedText($userDiscount, $quantity);
        if(!empty($bonus)){?>
            <script type="text/javascript">
                $(function(){
                    $('.js-bonus-<?=$offer->getId()?>').html('<?=$bonus?>');
                });
            </script>
        <?php }
    }
}
