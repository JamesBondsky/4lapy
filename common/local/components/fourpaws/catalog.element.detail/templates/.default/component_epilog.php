<?php

use FourPaws\Catalog\Model\Offer;
use FourPaws\Helpers\WordHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var Offer $currentOffer */
$currentOffer = $templateData['currentOffer'];
try {
    $bonus = $currentOffer->getBonuses($component->getCurrentUserService()->getDiscount());
} catch (\Exception $e) {
    $bonus = 0;
}
if ($bonus > 0) {
    $bonus = round($bonus, 2, PHP_ROUND_HALF_DOWN);
    $ost = $bonus - floor($bonus) * 100;
    $bonus = '+' . $bonus . ' ' . WordHelper::declension($ost > 0 ? $ost : floor($bonus),
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