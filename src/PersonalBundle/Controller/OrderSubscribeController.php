<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 04.09.2019
 * Time: 11:37
 */

namespace FourPaws\PersonalBundle\Controller;


use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class OrderSubscribeController
 *
 * @package FourPaws\PersonalBundle\Controller
 *
 * @Route("/order_subscribe")
 */
class OrderSubscribeController
{
    use LazyLoggerAwareTrait;

    /** @var OrderSubscribeService */
    private $orderSubscribeService;

    /** @var CurrentUserProviderInterface */
    private $userService;

    const KEY = 1093;

    /**
     * OrderSubscribeController constructor.
     *
     * @param OrderSubscribeService $orderSubscribeService
     */
    public function __construct()
    {
        $this->orderSubscribeService = Application::getInstance()->getContainer()->get('order_subscribe.service');
        $this->userService = Application::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
    }

    /**
     * @Route("/rollback/")
     */
    public function rollback(Request $request)
    {
        $responce = new Response();

        if($_GET['key'] != self::KEY){
            $responce->setContent('Доступ запрещён');
            return $responce;
        }
        if(!$_GET['date']){
            $responce->setContent('Укажите дату');
            return $responce;
        }

        $date = new DateTime($_GET['date']);

        $dateStart = (clone $date)->setTime(0,0,0);
        $dateEnd = (clone $date)->setTime(23,59,59);

        $params = [];
        $params['filter']['=UF_ACTIVE'] = 1;
        $params['filter'][] = [
            'LOGIC' => 'AND',
            ['>UF_LAST_CHECK' => $dateStart],
            ['<UF_LAST_CHECK' => $dateEnd],
        ];
        $params['order'] = [
            'UF_NEXT_DEL' => 'ASC',
            'UF_DELIVERY_TIME' => 'ASC',
        ];

        $checkOrdersList = $this->orderSubscribeService->getOrderSubscribeRepository()->findBy($params);
        $updated = [];

        /** @var OrderSubscribe $orderSubscribe */
        foreach ($checkOrdersList as $orderSubscribe){
            $orderSubscribe
                ->setNextDate($orderSubscribe->getPreviousDate())
                ->countDateCheck();

            $result = $this->orderSubscribeService->update($orderSubscribe);

            if(!$result->isSuccess()){
                return $responce->setContent(sprintf("Ошибка обновления подписки на доставку: %s [%s]", implode(',', $result->getErrorMessages()), $orderSubscribe->getId()));
            }

            $updated[] = [
                'ID' => $orderSubscribe->getId(),
                'DELIVERY_DATE' => $orderSubscribe->getNextDate()->toString(),
            ];
        }

        $responce->setContent(sprintf("Обновлено \r\n %s", json_encode($updated)));
        return $responce;
    }
}