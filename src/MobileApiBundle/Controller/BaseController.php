<?php


namespace FourPaws\MobileApiBundle\Controller;


use Bitrix\Main\Type\DateTime;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\App\Application as App;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\MobileApiBundle\Tables\UserApiLastUsingTable;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;

class BaseController extends FOSRestController
{
}
