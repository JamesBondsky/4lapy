<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;

class FourPawsRegisterComponent extends \CBitrixComponent
{
    /**
     * @param string $phone
     *
     * @return bool
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \RuntimeException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\External\Exception\SmsSendErrorException
     * @throws \FourPaws\User\Exceptions\WrongPhoneNumberException
     */
    public static function sendConfirmSms(string $phone) : bool
    {
        $phone = FourPaws\User\Utils::normalizePhone($phone);
        if (FourPaws\User\Utils::isPhone($phone)) {
            $generatedCode = static::generateCode($phone);
            
            if (!empty($generatedCode)) {
                $smsService = new FourPaws\External\SmsService();
                $text       = 'Ваш код подверждения - ' . $generatedCode;
                $smsService->sendSmsImmediate($text, $phone);
                return true;
            }
        }
        return false;
    }
    
    /**
     * @param string $phone
     *
     * @return bool|float|int
     */
    public static function generateCode(string $phone)
    {
        return empty($phone) ? false : hexdec(substr(md5($phone . 'salt4LPVerificationPhone'), 7, 5));
    }
    
    /**
     * @param string $phone
     * @param string $confirmCode
     *
     * @return bool
     * @throws \FourPaws\User\Exceptions\WrongPhoneNumberException
     */
    public static function checkConfirmSms(string $phone, string $confirmCode) : bool
    {
        $phone = FourPaws\User\Utils::normalizePhone($phone);
        if (FourPaws\User\Utils::isPhone($phone)) {
            $generatedCode = static::generateCode($phone);
            
            if (!empty($generatedCode)) {
                return $confirmCode === $generatedCode;
            }
        }
        
        return false;
    }
    
    /** {@inheritdoc} */
    public function onPrepareComponentParams($params) : array
    {
        return $params;
    
    }
    
    /** {@inheritdoc} */
    public function executeComponent()
    {
        try {
            //$this->manzana = new FourPaws\External\ManzanaService();
            $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
            $this->arResult['step'] = 1;
            
            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }
    
    protected function register()
    {
        $data = static::getDataFromRequest();
        if(isset($data['USER_ID']) && !empty($data['USER_ID'])){
            //$userID = $data['USER_ID'];
            //unset($data['USER_ID']);
            //$res = FourPaws\User\UserService::update($userID, $data);
            //if(!$res->isSuccess()){
            //    $this->arResult['ERRORS'][] = '';
            //}
        }
        else{
            $res = FourPaws\User\UserService::add($data);
            if(!$res->isSuccess()){
                $this->arResult['ERRORS'][] = 'При регистрации произошла ошибка, попробуйте позже';
            }
        }
    }
    
    public static function getDataFromRequest() : array
    {
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $data = $request->getPostList()->toArray();
        
        return $data;
    }
}