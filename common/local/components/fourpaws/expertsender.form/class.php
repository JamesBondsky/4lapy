<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\EcommerceBundle\Service\RetailRocketService;
use FourPaws\PersonalBundle\Service\PetService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use FourPaws\App\Application as App;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsExpertsenderFormComponent extends CBitrixComponent implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var RetailRocketService
     */
    private $retailRocketService;

    /**
     * FourPawsExpertsenderFormComponent constructor.
     *
     * @param CBitrixComponent|null $component
     *
     * @throws RuntimeException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);

        try {
            $container = Application::getInstance()->getContainer();

            $this->userService = $container->get(CurrentUserProviderInterface::class);
            $this->retailRocketService = $container->get(RetailRocketService::class);
        } catch (ApplicationCreateException | ServiceCircularReferenceException | ServiceNotFoundException $e) {
            $this->log()
                 ->critical(
                     \sprintf(
                         'Component execute error: [%s] %s in %s:%d',
                         $e->getCode(),
                         $e->getMessage(),
                         $e->getFile(),
                         $e->getLine()
                     )
                 );
        }
    }

    /**
     * @throws RuntimeException
     */
    public function executeComponent(): void
    {
        try {
            $this->arResult['EMAIL'] = '';
            $stringData = '';
            if ($this->userService->isAuthorized()) {
                $curUser = $this->userService->getCurrentUser();

                $this->arResult['EMAIL'] = $curUser->getEmail();
                $this->arResult['CONFIRMED'] = $curUser->isEmailConfirmed();
                $this->arResult['IS_SUBSCRIBED'] = $curUser->isEsSubscribed();
                $name = $curUser->getName();
                /** @var PetService $petService */
                $petService = App::getInstance()->getContainer()->get('pet.service');
                $petsTypes = $petService->getUserPetsTypesCodes($curUser->getId());
                $stringData .= ', {name: "' . $name . '"';
                foreach ($petsTypes as $key => $value) {
                    $stringData .= ', ' . $key . ': true';
                }
                $stringData .= '}';
            }

            $this->arResult['ON_SUBMIT'] = \str_replace('"', '\'',
                'if($(this).find("input[type=email]").val().indexOf("register.phone") == -1){' . $this->retailRocketService->renderSendEmail('$(this).find("input[type=email]").val()' . $stringData) . '}'
            );

            parent::executeComponent();
            $this->includeComponentTemplate();
        } catch (Throwable $e) {

            $this->log()
                 ->critical(
                     \sprintf(
                         'Component execute error: [%s] %s in %s:%d',
                         $e->getCode(),
                         $e->getMessage(),
                         $e->getFile(),
                         $e->getLine()
                     )
                 );
        }
    }
}
