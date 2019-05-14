<?php

namespace FourPaws\KkmBundle\Service;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Exception;
use FourPaws\App\Application;
use FourPaws\External\DaDataService;
use FourPaws\KkmBundle\Repository\Table\KkmTokenTable;
use Psr\Log\LoggerAwareInterface;

/**
 * Class KkmService
 *
 * @package FourPaws\KkmBundle\Service
 */
class KkmService implements LoggerAwareInterface
{
    const TOKEN_LENGTH = 16;
    const CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    use LazyLoggerAwareTrait;

    /**
     * @param string $token
     * @return array
     */
    public function validateToken($token): array
    {
        if (strlen($token) != static::TOKEN_LENGTH) {
            $res = [
                'success' => false,
                'error'   => 'Token Length error!'
            ];
        } else {
            $dbResult = KkmTokenTable::query()
                ->setSelect(['*'])
                ->setFilter(['token' => $token])
                ->exec();

            $tokensCnt = $dbResult->getSelectedRowsCount();
            switch ($tokensCnt) {
                case 0:
                    $res = [
                        'success' => false,
                        'error'   => 'Token not Found!'
                    ];
                    break;
                case 1:
                    $tokenData = $dbResult->fetch();
                    $res = [
                        'success' => true,
                        'id'      => $tokenData['id']
                    ];
                    break;
                default:
                    $res = [
                        'success' => false,
                        'error'   => 'Multiple token Found!'
                    ];
            }
        }

        if (!$res['success']) {
            $this->log()->error($res['error'], ['token' => $token]);
        }

        return $res;
    }

    /**
     * @return string
     * @throws Exception
     */
    private function generateToken(): string
    {
        $randomString = '';
        for ($i = 0; $i < static::TOKEN_LENGTH; $i++) {
            $randomString .= static::CHARACTERS[rand(0, strlen(static::CHARACTERS) - 1)];
        }
        return $randomString;
    }

    /**
     * @param string $id
     * @return array
     */
    public function updateToken(string $id): array
    {
        try {
            $token = $this->generateToken();
            KkmTokenTable::update(
                $id,
                [
                    'token' => $token
                ]
            );
            $res = [
                'success' => true,
                'token'   => $token
            ];
        } catch (Exception $e) {
            $res = [
                'success' => false,
                'error'   => $e->getMessage()
            ];
        }

        if (!$res['success']) {
            $this->log()->error($res['error']);
        } else {
            $this->log()->notice('kkm change token to ' . $token);
        }

        return $res;
    }

    /**
     * @param string $query
     * @return array
     */
    public function getSuggestions($query): array
    {
        //check text length
        if (mb_strlen($query) < 5) {
            $res = [
                'success' => false,
                'text'    => $query,
                'error'   => 'Text is too short'
            ];
        } else {
            /** @var DaDataService $daDataService */
            $daDataService = Application::getInstance()->getContainer()->get('dadata.service');
            $suggestions = $daDataService->getKkmSuggestions($query);
            foreach ($suggestions as $key => &$suggestion) {
                if($suggestion['value'] && $suggestion['data']['city_kladr_id']){
                    $suggestion = [
                        'address'  => $suggestion['value'],
                        'kladr_id' => $suggestion['data']['city_kladr_id']
                    ];
                } else {
                    unset($suggestions[$key]);
                }

            }
            $res = [
                'success'     => true,
                'text'        => $query,
                'suggestions' => $suggestions
            ];
        }

        if (!$res['success']) {
            $this->log()->error($res['error']);
        }

        return $res;
    }
}
