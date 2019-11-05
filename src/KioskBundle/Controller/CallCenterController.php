<?php


namespace FourPaws\KioskBundle\Controller;


use DateTime;
use FourPaws\AppBundle\Callback\CallbackService;
use FourPaws\StoreBundle\Service\StoreService;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CallCenterController
 * @package FourPaws\KioskBundle\Controller
 *
 * @Route("/callcenter")
 */
class CallCenterController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route("/add", methods={"POST"})
     */
    public function add(Request $request)
    {
        $rCode = $request->get('r_code');
        $sign = $request->get('sign');

        $answer = [
            'success' => true,
        ];

        $status = 200;

        try {
            $this->checkSign($sign, $rCode);

            $store = $this->getPhone($rCode);
            $this->sendData($store->getPhone());
        } catch (\Exception $exception) {
            $answer['success'] = false;
            $answer['errors'] = $exception->getMessage();
            $status = $exception->getCode();
        }
        return $this->json($answer, $status);
    }

    /**
     * Проверка sign
     * @param $sign
     * @param mixed ...$params
     * @return bool
     * @throws \Exception
     */
    private function checkSign($sign, ...$params)
    {
        $secretKey = getenv('KIOSK_SECRET_KEY');

        $strParam = '';

        foreach ($params as $paramItem) {
            $strParam .= $paramItem;
        }

        $strParam .= $secretKey;

        $strParam = md5($strParam);

        $checkSign = $strParam === $sign;

        if (!$checkSign) {
            throw new \Exception('Invalid sign', 401);
        }

        return $checkSign;
    }

    /**
     * Получение номера телефона
     * @param $rCode
     * @return \FourPaws\StoreBundle\Entity\Store
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     */
    private function getPhone($rCode)
    {
        /** @var StoreService $storeService */
        $storeService = $this->get('store.service');

        $res = $storeService->getStoreByXmlId($rCode);

        if (!$res) {
            new \Exception('Not found phone', 400);
        }

        return $res;
    }

    /**
     * Отправка данных в КЦ
     * @param $phone
     * @throws \Bitrix\Main\ObjectException
     */
    private function sendData($phone)
    {
        /** @var CallbackService $callbackService */
        $callbackService = $this->get('callback.service');

        [, $dopPhone] = explode('доб. ', $phone);

        $callbackService->send(
            $dopPhone,
            (new DateTime())->format('Y-m-d H:i:s')
        );
    }
}
