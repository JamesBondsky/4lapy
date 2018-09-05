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
        var $form = $('.js-form-faq');

        $form.find('.js-form-field-block-email input').val('<?= $user->getEmail() ?>').addClass('js-no-valid').blur();
        $form.find('.js-form-field-block-phone input').val('<?= $user->getPersonalPhone() ?>').addClass('js-no-valid').blur();
        $form.find('.js-form-field-block-name input').val('<?= $user->getName() ?>').addClass('js-no-valid').blur();
    </script>
<?php } catch (Exception $e) {
    /** пользователь неавторизован - устанавливать не надо */
}
