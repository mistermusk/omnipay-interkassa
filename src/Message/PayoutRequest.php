<?php

namespace Omnipay\InterKassa\Message;

use Omnipay\Common\Message\AbstractRequest;


class PayoutRequest extends AbstractRequest
{

    public function getCurrency()
    {
        return $this->getParameter('currency');
    }

    public function getTx()
    {
        return $this->getParameter('tx');
    }

    public function getMethod()
    {
        return $this->getParameter('method');
    }

    public function getAmount()
    {
        return $this->getParameter('amount');
    }

    public function setFullKeys($fullKeys){
        return $this->setParameter('fullKeys', $fullKeys);
    }

    public function getFullKeys()
    {
        return $this->getParameter('fullKeys');
    }

    public function getUserApi()
    {
        return $this->getFullKeys()['api']['shop_id'];
    }
    public function getKeyApi()
    {
        return $this->getFullKeys()['api']['secret_key'];
    }

    public function getShopId()
    {
        return $this->getFullKeys()[$this->getMethod()]['shop_id'];
    }
    public function getDetails()
    {
        return $this->getParameter('details');
    }
    public function setDetails($value)
    {
        return $this->setParameter('details', $value);
    }

    public function getSecretKey()
    {
        return $this->getFullKeys()[$this->getMethod()]['secret_key'];
    }


    public function setCurrency($value)
    {
        return $this->setParameter('currency', $value);
    }

    public function setTx($value)
    {
        return $this->setParameter('tx', $value);
    }

    public function setMethod($value)
    {
        return $this->setParameter('method', $value);
    }

    public function setAmount($value)
    {
        return $this->setParameter('amount', $value);
    }

    public function getData()
    {

        $data = [
            'purseId' => $this->getShopId(),
            'paymentNo' => $this->getTx(),
            'calcKey' => 'psPayeeAmount',
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'action' => 'process',
            'useShortAlias' => true,
            'method' => $this->getMethod(),
            'details' => $this->getDetails()

        ];

        return array_filter($data, function ($value) {
            return $value !== null;
        });

    }

    function sortByKeyRecursive(array $array): array
    {
        ksort($array, SORT_STRING);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->sortByKeyRecursive($value);
            }
        }
        return $array;
    }

    function implodeRecursive(string $separator, array $array): string
    {
        $result = '';
        foreach ($array as $item) {
            $result .= (is_array($item) ? $this->implodeRecursive($separator, $item) : (string)$item) . $separator;
        }

        return substr($result, 0, -1);
    }

    private function prepareMultipartData($data, $prefix = '')
    {
        $multipartData = [];
        foreach ($data as $key => $value) {
            $fullKey = $prefix . $key;
            if (is_array($value)) {
                // Обработка вложенных массивов
                foreach ($value as $subKey => $subValue) {
                    $multipartData[] = [
                        'name'     => "{$fullKey}[{$subKey}]",
                        'contents' => $subValue
                    ];
                }
            } else {
                // Обработка простых значений
                $multipartData[] = [
                    'name'     => $fullKey,
                    'contents' => $value
                ];
            }
        }
        return $multipartData;
    }


    public function sendData($data)
    {
        // Подготовка данных запроса и заголовков
        $userId = $this->getUserApi(); // Убедитесь, что это строка
        $apiKey = $this->getKeyApi(); // Убедитесь, что это строка
        $authHeaderValue = 'Basic ' . base64_encode($userId . ':' . $apiKey);

        $checkoutKey = $this->getSecretKey();
        $sortedDataByKeys = $this->sortByKeyRecursive($data);
        $sortedDataByKeys[] = $checkoutKey;
        $signString = $this->implodeRecursive(':', $sortedDataByKeys);
        $data['ik_sign'] = base64_encode(hash('sha256', $signString, true));

        $multipartData = $this->prepareMultipartData($data);

        // Создаем HTTP-клиент Guzzle
        $client = new \GuzzleHttp\Client();

        // Выполняем запрос
        try {
            $response = $client->request('POST', 'https://api.interkassa.com/v1/withdraw', [
                'headers' => [
                    'Authorization' => $authHeaderValue,
                    'Ik-Api-Account-Id' => $userId,
                ],
                'multipart' => $multipartData
            ]);
        } catch (\Exception $e) {
            // Обработка ошибок запроса
            // Можно логировать или пробрасывать исключение дальше
            error_log($e->getMessage());
            throw $e;
        }

        // Обработка ответа для Omnipay
        return $this->response = $this->createResponse($response->getBody()->getContents());
    }






    protected function createResponse($data)
    {
        return $this->response = new PayoutResponse($this, json_decode($data, true));
    }


}
