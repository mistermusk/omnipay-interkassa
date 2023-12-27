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

    public function getLevel()
    {
        if ($this->getParameter('level'))
            return 'first_level';
        return 'second_level';
    }
    public function setLevel($value)
    {
        return $this->setParameter('level', $value);
    }


    public function getMethod()
    {
        return $this->getParameter('method');
    }

    public function getAmount()
    {
        return $this->getParameter('amount');
    }

    public function setKeys($fullKeys){
        return $this->setParameter('keys', $fullKeys);
    }

    public function getKeys()
    {
        return $this->getParameter('keys');
    }

    public function getApikey()
    {
        return $this->getKeys()['api_deposit'][$this->getLevel()][$this->getMethod()][$this->getCurrency()]['api_key'];
    }

    public function getSecretKey()
    {
        return $this->getKeys()['api_deposit'][$this->getLevel()][$this->getCurrency()]['secret_key'];
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

    public function getRedirecturl()
    {
        return $this->getKeys()['redirect_url'];
    }

    public function getCallbackurl()
    {
        return $this->getParameter('callback_url');
    }
    public function setCallbackurl($value)
    {
        return $this->setParameter('callback_url', $value);
    }

    public function getData()
    {

        $data = [
            'ik_co_id' => $this->getApikey(),
            'ik_pm_no' => $this->getTx(),
            'ik_am' => $this->getAmount(),
            'ik_cur' => $this->getCurrency(),
            'ik_desc' => $this->getTx(),
            'ik_suc_u' => $this->getRedirecturl(),
            'ik_fal_u' => $this->getRedirecturl(),
            'ik_ia_u' => $this->getCallbackurl(),
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

