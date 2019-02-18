<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Sale\Order;
use FourPaws\UserBundle\Entity\User;

/**
 * @var User  $user
 * @var Order $order
 */
$user  = $arParams['ADDITIONAL_DATA']['USER'];
$order = $arParams['ADDITIONAL_DATA']['ORDER'];
try { ?>
    <script>
        $(function () {
            var $form = $('form.b-interview');
            $form.find('input[data-code=clientid]').val('<?=$user->getId()?>');
            $form.find('input[data-code=name]').val('<?=$user->getFullName()?>');
            $form.find('input[data-code=email]').val('<?=$user->getEmail()?>');
            $form.find('input[data-code=phone]').val('<?=$user->getPersonalPhone()?>');
            $form.find('input[data-code=order]').val('<?=$order->getFields()->get('ACCOUNT_NUMBER')?>');
        });
    </script>
<? } catch (Throwable $e) {
    /* Строго говоря, мы не можем сюда попать, потому что без $user или $order Exception возникнет раньше */
} ?>


