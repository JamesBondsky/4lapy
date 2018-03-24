<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

try {
    $container = App::getInstance()->getContainer();
    if ($container->get(UserAuthorizationInterface::class)->isAuthorized()) {
        $user = $container->get(CurrentUserProviderInterface::class)->getCurrentUser(); ?>
        <script>
            $(function () {
                var $form = $('.js-form-faq');
                $form.find('js-form-field-block-name input').val('<?=$user->getName()?>');
                $form.find('js-form-field-block-email input').val('<?=$user->getEmail()?>');
                $form.find('js-form-field-block-phone input').val('<?=$user->getPersonalPhone()?>');
            });
        </script>
    <?php }
} catch (ApplicationCreateException|ServiceNotFoundException|ServiceCircularReferenceException $e) {
    /** не будут установлены значения */
    $logger = LoggerFactory::create('form.result.new:faq');
    $logger->error('ошибка загрузки сервиса');
} catch (NotAuthorizedException $e) {
    /** пользователь неавторизован - устанавливать не надо */
}

