<?php

use FourPaws\Catalog\Model\Offer;
use FourPaws\Helpers\WordHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var Offer $currentOffer */
$currentOffer = $templateData['currentOffer'];
$bonus = $currentOffer->getBonusFormattedText($component->getCurrentUserService()->getDiscount());
if (!empty($bonus)) { ?>
    <script type="text/javascript">
        $(function() {
            $('.js-bonus-<?=$currentOffer->getId()?>').html('<?=$bonus?>');
        });
    </script>
<?php }