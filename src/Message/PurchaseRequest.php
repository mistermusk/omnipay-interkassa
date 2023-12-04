<?php


namespace Omnipay\InterKassa\Message;

use Omnipay\Common\Message\AbstractRequest;

class PurchaseRequest extends AbstractRequest
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

    public function getShopId()
    {
        return $this->getFullKeys()[$this->getMethod()]['shop_id'];
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
            'ik_co_id' => $this->getShopId(),
            'ik_pm_no' => $this->getTx(),
            'ik_am' => $this->getAmount(),
            'ik_cur' => $this->getCurrency(),
            'ik_desc' => 'interkassa',
            'ik_act' => 'process',
            'ik_int' => 'json',
            'ik_payment_method' => $this->getMethod(),
            'ik_payment_currency' => $this->getCurrency(),

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


    public function sendData($data)
    {


        $checkoutKey = $this->getSecretKey();
        $sortedDataByKeys = $this->sortByKeyRecursive($data);
        $sortedDataByKeys[] = $checkoutKey;

        $signString = $this->implodeRecursive(':', $sortedDataByKeys);
        $data['ik_sign'] = base64_encode(hash('sha256', $signString, true));

        $postData = http_build_query($data);

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $httpResponse = $this->httpClient->request('POST', 'https://sci.interkassa.com/', $headers, $postData);
        return $this->createResponse($httpResponse->getBody()->getContents());
    }


    protected function createResponse($data)
    {
        return $this->response = new PurchaseResponse($this, json_decode($data, true));
    }

}

