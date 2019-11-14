<?php


namespace FourPaws\KioskBundle\Controller;


use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use DateTime;
use Exception;
use FourPaws\App\Application as App;
use FourPaws\AppBundle\Callback\CallbackService;
use FourPaws\StoreBundle\Service\StoreService;
use GuzzleHttp\Exception\ClientException;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
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
    use LoggerAwareTrait;

    public function __construct()
    {
        $this->setLogger(LoggerFactory::create('callCenter', 'callCenter'));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route("/add", methods={"POST"})
     */
    public function add(Request $request)
    {
        [$rCode, $sign] = $this->getParams($request);

        $answer = [
            'success' => true,
        ];

        $status = 200;

        try {
            $secretKey = getenv('KIOSK_SECRET_KEY');

            $rightSign = $this->generateSign($secretKey, $rCode);
            $this->checkSign($sign, $rightSign);

            $phone = $this->getPhone($rCode);
            $additionalPhone = $this->getAdditionalPhone($phone);
            $this->sendData($additionalPhone);
        } catch (Exception $exception) {
            $this->log()->error($exception->getMessage(), [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace()
            ]);
            $answer['success'] = false;
            $status = ($exception->getCode() == 0 ? 400 : $exception->getCode());

            $answer['errors'][] = [
                'code' => $status,
                'error' => $exception->getMessage(),
            ];
        }
        return $this->json($answer, $status);
    }

    /**
     * Проверка sign
     * @param $sign
     * @param $rightSign
     * @return bool
     * @throws Exception
     */
    private function checkSign($sign, $rightSign)
    {
        $checkSign = $rightSign === $sign;

        if (!$checkSign) {
            throw new Exception('Invalid sign', 401);
        }

        return $checkSign;
    }

    /**
     * Получение номера телефона
     * @param $rCode
     * @return string
     * @throws \FourPaws\StoreBundle\Exception\NotFoundException
     * @throws Exception
     * @throws Exception
     */
    private function getPhone($rCode)
    {
        /** @var StoreService $storeService */
        $storeService = $this->get('store.service');

        $res = $storeService->getStoreByXmlId($rCode);

        if (!$res) {
            throw new Exception('Not found store', 400);
        }

        $phone = $res->getPhone();
        if (!$phone) {
            throw new Exception('Not found phone', 400);
        }

        return $phone;
    }

    /**
     * Отправка данных в КЦ
     * @param $phone
     * @throws \Bitrix\Main\ObjectException
     */
    private function sendData($phone)
    {
        /** @var \GuzzleHttp\Client $guzzleClient */
        $guzzleClient = App::getInstance()->getContainer()->get('manzana.guzzle');

        $url = getenv('KIOSK_CALLBACK_URI');
        $api = getenv('KIOSK_CALLBACK_API');

        $params = [
            'number' => $phone,
            'apikey' => $api,
        ];
        try {
            $guzzleClient->get($url . '?' . http_build_query($params));
        } catch (ClientException $e) {
            $response = $e->getResponse();

            if ((string)$response->getBody() != '200') {
                throw new Exception('Don\'t send request CallCenter', 400);
            }
        }
    }

    /**
     * Получение дополнительного номера телефона из основного
     * @param $phone
     * @return mixed
     * @throws Exception
     */
    private function getAdditionalPhone($phone)
    {
        [, $additionalPhone] = explode('доб.', $phone);
        $additionalPhone = trim($additionalPhone);

        if (!$additionalPhone) {
            throw new Exception('Not found dop phone');
        }

        return $additionalPhone;
    }

    /**
     * Генерирует sign исходя из переданных параметров
     * @param $secretKey
     * @param mixed ...$params
     * @return string
     */
    private function generateSign($secretKey, ...$params)
    {
        $strParam = implode('', $params);

        $strParam .= $secretKey;

        $strParam = md5($strParam);

        return $strParam;
    }

    /**
     * Получение и обработка параметров
     * @param Request $request
     * @return array
     */
    private function getParams(Request $request)
    {
        $rCode = $request->get('r_code');
        $sign = $request->get('sign');

        $rCode = trim($rCode);
        $sign = trim($sign);

        return [$rCode, $sign];
    }

    /**
     * @return LoggerInterface
     */
    private function log() : LoggerInterface
    {
        return $this->logger;
    }
}
