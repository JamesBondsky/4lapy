<?php

use FourPaws\Catalog\Model\Offer;
use FourPaws\Helpers\WordHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var Offer $currentOffer */
$currentOffer = $templateData['currentOffer'];
try {
    $bonuses = $currentOffer->getBonuses($component->getCurrentUserService()->getDiscount());
} catch (\Exception $e) {
    $bonuses = 0;
}
if ($bonuses > 0) {
    $bonuses = round($bonuses, 2, PHP_ROUND_HALF_DOWN);
    $ost = $bonuses - floor($bonuses) * 100;
    $bonus = '+' . $bonuses . ' ' . WordHelper::declension($ost > 0 ? $ost : floor($bonuses),
            [
                'бонус',
                'бонуса',
                'бонусов',
            ]) ?>
    <script type="text/javascript">
        $(function() {
            $('.js-bonus-<?=$currentOffer->getId()?>').html('<?=$bonus?>');
        });
    </script>
<?php }