<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application as App;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;

try {
    $user = App::getInstance()
        ->getContainer()
        ->get(CurrentUserProviderInterface::class)
        ->getCurrentUser(); ?>
    <script>
        $(function () {
            var $form = $('.js-form-faq');
            $form.find('.js-form-field-block-name input').val('<?= $user->getName() ?>');
            $form.find('.js-form-field-block-email input').val('<?= $user->getEmail() ?>');
            $form.find('.js-form-field-block-phone input').val('<?= $user->getPersonalPhone() ?>');
        });
    </script>
<?php } catch (Exception $e) {
    /** пользователь неавторизован - устанавливать не надо */
}
